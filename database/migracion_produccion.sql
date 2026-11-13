-- =====================================================================================================
-- Migracion de produccion: claves primarias, AUTO_INCREMENT y columnas del flujo de pagos
-- Sistema de Gestion - Escuela de Beisbol Menor Maporal
--
-- PARA QUE SIRVE
--   Una base creada con el esquema anterior deja tablas SIN clave primaria ni AUTO_INCREMENT (payment_history,
--   send_log, send_counters, payment_concepts, cuenta bancaria) y sin las columnas del flujo de verificacion en
--   payments. Sintoma tipico: el representante reporta un pago y "no pasa nada" (el INSERT en payment_history
--   no tiene id y la transaccion se revierte), o el cobro se crea pero muestra error.
--
-- COMO USARLO
--   1. HAGA UN RESPALDO ANTES (phpMyAdmin > Exportar).
--   2. phpMyAdmin > seleccione la base de datos > pestana SQL > pegue este archivo completo > Continuar.
--      (o: mysql -u USUARIO -p NOMBRE_BD < migracion_produccion.sql)
--
-- ES SEGURO REPETIRLO: cada paso comprueba primero si hace falta. En una base ya correcta no cambia nada.
-- Las bitacoras (payment_history, send_log, historial de cuenta bancaria) se deduplican (filas copiadas tal
-- cual) y se renumeran SOLO si no tenian clave primaria; ninguna otra tabla referencia esos ids.
-- =====================================================================================================

SET FOREIGN_KEY_CHECKS = 0;

DROP PROCEDURE IF EXISTS maporal_exec_if;
DELIMITER $$
CREATE PROCEDURE maporal_exec_if(IN cond INT, IN stmt TEXT)
BEGIN
    IF cond THEN
        SET @maporal_sql = stmt;
        PREPARE maporal_stmt FROM @maporal_sql;
        EXECUTE maporal_stmt;
        DEALLOCATE PREPARE maporal_stmt;
    END IF;
END$$
DELIMITER ;

-- -----------------------------------------------------------------------------------------------------
-- 1. payment_concepts (catalogo de conceptos): crear si no existe, deduplicar, PK + AUTO_INCREMENT + UNIQUE
-- -----------------------------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payment_concepts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `default_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_monthly` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = se cobra por mes (mensualidad)',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_concept_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET @no_pk = (SELECT COUNT(*) = 0 FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_concepts' AND CONSTRAINT_TYPE = 'PRIMARY KEY');
CALL maporal_exec_if(@no_pk, 'CREATE TEMPORARY TABLE maporal_tmp AS SELECT DISTINCT * FROM payment_concepts');
CALL maporal_exec_if(@no_pk, 'DELETE FROM payment_concepts');
CALL maporal_exec_if(@no_pk, 'INSERT INTO payment_concepts SELECT * FROM maporal_tmp');
CALL maporal_exec_if(@no_pk, 'DROP TEMPORARY TABLE maporal_tmp');
CALL maporal_exec_if(@no_pk, 'ALTER TABLE payment_concepts ADD PRIMARY KEY (id)');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_concepts' AND COLUMN_NAME = 'id' AND EXTRA LIKE '%auto_increment%'),
                     'ALTER TABLE payment_concepts MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.STATISTICS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_concepts' AND INDEX_NAME = 'uq_concept_name'),
                     'ALTER TABLE payment_concepts ADD UNIQUE KEY uq_concept_name (name)');

-- Conceptos iniciales solo si el catalogo esta vacio (los montos se ajustan luego en el modulo Conceptos).
INSERT INTO payment_concepts (name, description, default_amount, is_monthly, is_active)
SELECT * FROM (
    SELECT 'Mensualidad' AS name, 'Cuota mensual de la escuela' AS description, 10000.00 AS default_amount, 1 AS is_monthly, 1 AS is_active
    UNION ALL SELECT 'Inscripcion', 'Pago unico de inscripcion del atleta', 30000.00, 0, 1
    UNION ALL SELECT 'Uniforme', 'Uniforme deportivo de la escuela', 40000.00, 0, 1
) seed
WHERE NOT EXISTS (SELECT 1 FROM payment_concepts);

-- -----------------------------------------------------------------------------------------------------
-- 2. payments: columnas del flujo de verificacion y estados nuevos
-- -----------------------------------------------------------------------------------------------------
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'concept_id'),
                     'ALTER TABLE payments ADD COLUMN concept_id INT(11) DEFAULT NULL AFTER athlete_id');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reference_number'),
                     'ALTER TABLE payments ADD COLUMN reference_number VARCHAR(40) DEFAULT NULL AFTER amount');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'payment_method'),
                     'ALTER TABLE payments ADD COLUMN payment_method VARCHAR(30) DEFAULT NULL AFTER reference_number');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reported_by'),
                     'ALTER TABLE payments ADD COLUMN reported_by INT(11) DEFAULT NULL AFTER notes');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reported_at'),
                     'ALTER TABLE payments ADD COLUMN reported_at DATETIME DEFAULT NULL AFTER reported_by');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reviewed_by'),
                     'ALTER TABLE payments ADD COLUMN reviewed_by INT(11) DEFAULT NULL AFTER reported_at');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'reviewed_at'),
                     'ALTER TABLE payments ADD COLUMN reviewed_at DATETIME DEFAULT NULL AFTER reviewed_by');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'review_comment'),
                     'ALTER TABLE payments ADD COLUMN review_comment VARCHAR(500) DEFAULT NULL AFTER reviewed_at');

-- Estados: pending/paid (viejo) -> pending/in_review/approved/rejected. Un pago 'paid' ya estaba cobrado: pasa a 'approved'.
SET @old_status = (SELECT COUNT(*) FROM information_schema.COLUMNS
                   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'status' AND COLUMN_TYPE NOT LIKE '%in_review%');
CALL maporal_exec_if(@old_status, 'ALTER TABLE payments MODIFY status ENUM(''pending'',''paid'',''in_review'',''approved'',''rejected'') NOT NULL DEFAULT ''pending''');
CALL maporal_exec_if(@old_status, 'UPDATE payments SET status = ''approved'' WHERE status = ''paid''');
CALL maporal_exec_if(@old_status, 'ALTER TABLE payments MODIFY status ENUM(''pending'',''in_review'',''approved'',''rejected'') NOT NULL DEFAULT ''pending''');

CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_reference'),
                     'ALTER TABLE payments ADD KEY idx_payments_reference (reference_number)');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND INDEX_NAME = 'idx_payments_status_reported'),
                     'ALTER TABLE payments ADD KEY idx_payments_status_reported (status, reported_at)');

-- -----------------------------------------------------------------------------------------------------
-- 3. payment_history (bitacora de pagos): la que bloquea el reporte del representante si no tiene AUTO_INCREMENT
-- -----------------------------------------------------------------------------------------------------
SET @no_pk = (SELECT COUNT(*) = 0 FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_history' AND CONSTRAINT_TYPE = 'PRIMARY KEY');
CALL maporal_exec_if(@no_pk, 'CREATE TEMPORARY TABLE maporal_tmp AS SELECT DISTINCT * FROM payment_history');
CALL maporal_exec_if(@no_pk, 'DELETE FROM payment_history');
CALL maporal_exec_if(@no_pk, 'INSERT INTO payment_history SELECT * FROM maporal_tmp');
CALL maporal_exec_if(@no_pk, 'DROP TEMPORARY TABLE maporal_tmp');
SET @maporal_n = 0;
CALL maporal_exec_if(@no_pk, 'UPDATE payment_history SET id = (@maporal_n := @maporal_n + 1) ORDER BY created_at, id');
CALL maporal_exec_if(@no_pk, 'ALTER TABLE payment_history ADD PRIMARY KEY (id)');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_history' AND COLUMN_NAME = 'id' AND EXTRA LIKE '%auto_increment%'),
                     'ALTER TABLE payment_history MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_history' AND INDEX_NAME = 'idx_history_payment'),
                     'ALTER TABLE payment_history ADD KEY idx_history_payment (payment_id, id)');

-- -----------------------------------------------------------------------------------------------------
-- 4. send_log y send_counters (limite diario de correos/WhatsApp)
-- -----------------------------------------------------------------------------------------------------
SET @no_pk = (SELECT COUNT(*) = 0 FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'send_log' AND CONSTRAINT_TYPE = 'PRIMARY KEY');
CALL maporal_exec_if(@no_pk, 'CREATE TEMPORARY TABLE maporal_tmp AS SELECT DISTINCT * FROM send_log');
CALL maporal_exec_if(@no_pk, 'DELETE FROM send_log');
CALL maporal_exec_if(@no_pk, 'INSERT INTO send_log SELECT * FROM maporal_tmp');
CALL maporal_exec_if(@no_pk, 'DROP TEMPORARY TABLE maporal_tmp');
SET @maporal_n = 0;
CALL maporal_exec_if(@no_pk, 'UPDATE send_log SET id = (@maporal_n := @maporal_n + 1) ORDER BY created_at, id');
CALL maporal_exec_if(@no_pk, 'ALTER TABLE send_log ADD PRIMARY KEY (id)');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'send_log' AND COLUMN_NAME = 'id' AND EXTRA LIKE '%auto_increment%'),
                     'ALTER TABLE send_log MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'send_log' AND INDEX_NAME = 'idx_send_log_day'),
                     'ALTER TABLE send_log ADD KEY idx_send_log_day (channel, created_at)');

-- send_counters necesita PK (channel, day): sin ella el INSERT ... ON DUPLICATE KEY nunca suma y el limite diario no funciona.
SET @no_pk = (SELECT COUNT(*) = 0 FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'send_counters' AND CONSTRAINT_TYPE = 'PRIMARY KEY');
CALL maporal_exec_if(@no_pk, 'CREATE TEMPORARY TABLE maporal_tmp AS SELECT channel, day, SUM(used) AS used FROM send_counters GROUP BY channel, day');
CALL maporal_exec_if(@no_pk, 'DELETE FROM send_counters');
CALL maporal_exec_if(@no_pk, 'INSERT INTO send_counters (channel, day, used) SELECT channel, day, used FROM maporal_tmp');
CALL maporal_exec_if(@no_pk, 'DROP TEMPORARY TABLE maporal_tmp');
CALL maporal_exec_if(@no_pk, 'ALTER TABLE send_counters ADD PRIMARY KEY (channel, day)');

-- -----------------------------------------------------------------------------------------------------
-- 5. Cuenta bancaria de la escuela (una sola fila) y su historial
-- -----------------------------------------------------------------------------------------------------
SET @no_pk = (SELECT COUNT(*) = 0 FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'school_bank_account' AND CONSTRAINT_TYPE = 'PRIMARY KEY');
CALL maporal_exec_if(@no_pk, 'CREATE TEMPORARY TABLE maporal_tmp AS SELECT * FROM school_bank_account ORDER BY updated_at DESC LIMIT 1');
CALL maporal_exec_if(@no_pk, 'DELETE FROM school_bank_account');
CALL maporal_exec_if(@no_pk, 'INSERT INTO school_bank_account SELECT * FROM maporal_tmp');
CALL maporal_exec_if(@no_pk, 'DROP TEMPORARY TABLE maporal_tmp');
CALL maporal_exec_if(@no_pk, 'ALTER TABLE school_bank_account ADD PRIMARY KEY (id)');

SET @no_pk = (SELECT COUNT(*) = 0 FROM information_schema.TABLE_CONSTRAINTS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'school_bank_account_history' AND CONSTRAINT_TYPE = 'PRIMARY KEY');
CALL maporal_exec_if(@no_pk, 'CREATE TEMPORARY TABLE maporal_tmp AS SELECT DISTINCT * FROM school_bank_account_history');
CALL maporal_exec_if(@no_pk, 'DELETE FROM school_bank_account_history');
CALL maporal_exec_if(@no_pk, 'INSERT INTO school_bank_account_history SELECT * FROM maporal_tmp');
CALL maporal_exec_if(@no_pk, 'DROP TEMPORARY TABLE maporal_tmp');
SET @maporal_n = 0;
CALL maporal_exec_if(@no_pk, 'UPDATE school_bank_account_history SET id = (@maporal_n := @maporal_n + 1) ORDER BY created_at, id');
CALL maporal_exec_if(@no_pk, 'ALTER TABLE school_bank_account_history ADD PRIMARY KEY (id)');
CALL maporal_exec_if((SELECT COUNT(*) = 0 FROM information_schema.COLUMNS
                      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'school_bank_account_history' AND COLUMN_NAME = 'id' AND EXTRA LIKE '%auto_increment%'),
                     'ALTER TABLE school_bank_account_history MODIFY id INT(11) NOT NULL AUTO_INCREMENT');

-- -----------------------------------------------------------------------------------------------------
-- 6. AUTO_INCREMENT en el id del resto de tablas (si tienen PK pero perdieron el AUTO_INCREMENT)
-- -----------------------------------------------------------------------------------------------------
CALL maporal_exec_if((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admins' AND COLUMN_NAME = 'id' AND COLUMN_KEY = 'PRI' AND EXTRA NOT LIKE '%auto_increment%'),
                     'ALTER TABLE admins MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'athletes' AND COLUMN_NAME = 'id' AND COLUMN_KEY = 'PRI' AND EXTRA NOT LIKE '%auto_increment%'),
                     'ALTER TABLE athletes MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payments' AND COLUMN_NAME = 'id' AND COLUMN_KEY = 'PRI' AND EXTRA NOT LIKE '%auto_increment%'),
                     'ALTER TABLE payments MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pdf_verifications' AND COLUMN_NAME = 'id' AND COLUMN_KEY = 'PRI' AND EXTRA NOT LIKE '%auto_increment%'),
                     'ALTER TABLE pdf_verifications MODIFY id INT(11) NOT NULL AUTO_INCREMENT');
CALL maporal_exec_if((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'membership_certificates' AND COLUMN_NAME = 'id' AND COLUMN_KEY = 'PRI' AND EXTRA NOT LIKE '%auto_increment%'),
                     'ALTER TABLE membership_certificates MODIFY id INT(11) NOT NULL AUTO_INCREMENT');

DROP PROCEDURE IF EXISTS maporal_exec_if;
SET FOREIGN_KEY_CHECKS = 1;

-- Verificacion: las tablas clave deben mostrar 'auto_increment' en EXTRA.
SELECT TABLE_NAME, COLUMN_NAME, COLUMN_KEY, EXTRA
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND COLUMN_NAME = 'id'
  AND TABLE_NAME IN ('payments', 'payment_history', 'send_log', 'payment_concepts', 'admins', 'athletes')
ORDER BY TABLE_NAME;
