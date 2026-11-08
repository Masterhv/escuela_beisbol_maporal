<?php
require_once 'models/Model.php';

/**
 * Datos bancarios de la escuela: la cuenta (transferencia) y el pago movil donde los representantes depositan.
 *
 * - Es UNA sola fila (id = 1): son los mismos para todos los conceptos de cobro. Se editan en Conceptos (solo administrador).
 * - Se muestran al representante en el formulario de reportar un pago, para que deposite en la cuenta correcta.
 * - Cambiarlos es sensible: quien altere el numero de cuenta desviaria los pagos de las familias. Por eso cada cambio queda
 *   en school_bank_account_history con QUIEN, CUANDO y los valores ANTERIORES (para detectarlo y revertirlo).
 * - Todo es opcional: la escuela puede tener solo transferencia, solo pago movil o ambos. Nunca se inventa un dato: mientras
 *   no se carguen, el representante ve un aviso de que la escuela aun no registro sus datos de pago.
 */
class SchoolBankAccount extends Model {

    protected $table = 'school_bank_account';

    /** Campos editables y su etiqueta (la misma que ve el administrador en la bitacora). */
    public const FIELDS = [
        'holder_name'    => 'Titular',
        'holder_id'      => 'Cédula/RIF del titular',
        'bank_name'      => 'Banco',
        'account_type'   => 'Tipo de cuenta',
        'account_number' => 'Número de cuenta',
        'mobile_bank'    => 'Banco del pago móvil',
        'mobile_phone'   => 'Teléfono del pago móvil',
        'mobile_id'      => 'Cédula/RIF del pago móvil',
    ];

    /** Los datos actuales (siempre devuelve las 8 claves; vacias si no se han cargado). */
    public function get() {
        $row = $this->db->query("SELECT * FROM {$this->table} WHERE id = 1")->fetch(PDO::FETCH_ASSOC) ?: [];
        $out = [];
        foreach (array_keys(self::FIELDS) as $k) { $out[$k] = $row[$k] ?? null; }
        $out['updated_at'] = $row['updated_at'] ?? null;
        $out['updated_by_name'] = $row['updated_by_name'] ?? null;
        return $out;
    }

    /** ¿Hay transferencia completa / pago movil completo? Para decidir que le muestra la vista al representante. */
    public static function hasTransfer(array $d) {
        return !empty($d['account_number']) && !empty($d['bank_name']);
    }
    public static function hasMobile(array $d) {
        return !empty($d['mobile_phone']) && !empty($d['mobile_bank']);
    }
    public static function isConfigured(array $d) {
        return self::hasTransfer($d) || self::hasMobile($d);
    }

    /**
     * Normaliza la entrada: recorta, colapsa espacios, deja cedula/RIF y telefono en un formato unico y pasa a mayusculas
     * lo que corresponde. Asi "0414 123 4567", "V12345678" o "j-12345678-9" se aceptan y se guardan siempre igual (el
     * representante copia estos datos al banco; un formato ambiguo provoca errores de deposito).
     */
    public static function normalize(array $in) {
        $clean = fn($v) => preg_replace('/\s+/u', ' ', trim((string)$v));
        $out = [];
        foreach (array_keys(self::FIELDS) as $k) { $out[$k] = $clean($in[$k] ?? ''); }
        // Cedula/RIF: V-12345678 | E-12345678 | J-12345678-9 | G-12345678-9.
        // Una cedula tiene 6-8 digitos; un RIF (J, G, P) suma 1 digito verificador. Por eso 9 digitos seguidos tras J/G/P solo pueden ser
        // 8 + 1 (se separan), y el digito verificador es opcional. Se acepta con o sin guiones, puntos o espacios.
        foreach (['holder_id', 'mobile_id'] as $k) {
            if ($out[$k] !== '' && preg_match('/^([JGPjgp])[\s\-\.]*([0-9]{8})[\s\-\.]*([0-9])$/', $out[$k], $m)) {
                $out[$k] = strtoupper($m[1]) . '-' . $m[2] . '-' . $m[3];
            } elseif ($out[$k] !== '' && preg_match('/^([VEJGPvejgp])[\s\-\.]*([0-9]{6,8})(?:[\s\-\.]*([0-9]))?$/', $out[$k], $m)) {
                $out[$k] = strtoupper($m[1]) . '-' . $m[2] . (isset($m[3]) && $m[3] !== '' ? '-' . $m[3] : '');
            } elseif ($out[$k] !== '' && preg_match('/^[0-9]{6,9}$/', $out[$k])) {
                $out[$k] = 'V-' . $out[$k];   // solo digitos: se entiende cedula venezolana
            }
        }
        // Telefono: 0414-1234567 (admite espacios, puntos o guiones)
        if ($out['mobile_phone'] !== '') {
            $digits = preg_replace('/\D+/', '', $out['mobile_phone']);
            if (strlen($digits) === 11) { $out['mobile_phone'] = substr($digits, 0, 4) . '-' . substr($digits, 4); }
        }
        // Cuenta: solo digitos
        if ($out['account_number'] !== '') { $out['account_number'] = preg_replace('/[\s\-\.]+/', '', $out['account_number']); }
        $out['account_type'] = strtolower($out['account_type']);
        return $out;
    }

    /**
     * Valida los datos YA normalizados. Devuelve una lista de mensajes de error (vacia = correcto).
     *
     * Reglas: todo es opcional, pero cada grupo debe estar COMPLETO si se empieza (no se guarda media transferencia, que
     * confundiria al representante): transferencia = banco + tipo + cuenta; pago movil = banco + telefono + cedula/RIF.
     * El titular y su cedula/RIF son obligatorios si hay cualquier dato de pago.
     */
    public static function validate(array $d) {
        $e = [];
        $len = fn($s) => mb_strlen((string)$s);
        $any = fn(array $ks) => array_filter($ks, fn($k) => $d[$k] !== '');
        $transferKeys = ['bank_name', 'account_type', 'account_number'];
        $mobileKeys = ['mobile_bank', 'mobile_phone', 'mobile_id'];
        $hasTransfer = (bool)$any($transferKeys); $hasMobile = (bool)$any($mobileKeys);

        if (($hasTransfer || $hasMobile) && $d['holder_name'] === '') { $e[] = 'Indica el titular de la cuenta.'; }
        if (($hasTransfer || $hasMobile) && $d['holder_id'] === '') { $e[] = 'Indica la cédula o RIF del titular.'; }
        if ($d['holder_name'] !== '' && ($len($d['holder_name']) < 3 || $len($d['holder_name']) > 120 || !preg_match('/^[\p{L}0-9\s\.\,\-&\'()]+$/u', $d['holder_name']))) {
            $e[] = 'El titular solo puede llevar letras, números, espacios y . , - & \' ( ), entre 3 y 120 caracteres.';
        }
        foreach (['holder_id' => 'del titular', 'mobile_id' => 'del pago móvil'] as $k => $lbl) {
            if ($d[$k] !== '' && !preg_match('/^[VEJGP]-[0-9]{6,9}(-[0-9])?$/', $d[$k])) { $e[] = "La cédula o RIF $lbl no es válido (ejemplos: V-12345678, J-12345678-9)."; }
        }
        if ($hasTransfer) {
            foreach (['bank_name' => 'el banco', 'account_type' => 'el tipo de cuenta', 'account_number' => 'el número de cuenta'] as $k => $lbl) { if ($d[$k] === '') { $e[] = "La transferencia está incompleta: falta $lbl."; } }
            if ($d['bank_name'] !== '' && ($len($d['bank_name']) < 3 || $len($d['bank_name']) > 80 || !preg_match('/^[\p{L}0-9\s\.\-&\'()]+$/u', $d['bank_name']))) { $e[] = 'El nombre del banco no es válido (letras, números y espacios, entre 3 y 80 caracteres).'; }
            if ($d['account_type'] !== '' && !in_array($d['account_type'], ['corriente', 'ahorro'], true)) { $e[] = 'El tipo de cuenta debe ser corriente o ahorro.'; }
            if ($d['account_number'] !== '' && !preg_match('/^[0-9]{20}$/', $d['account_number'])) { $e[] = 'El número de cuenta debe tener exactamente 20 dígitos.'; }
        }
        if ($hasMobile) {
            foreach (['mobile_bank' => 'el banco', 'mobile_phone' => 'el teléfono', 'mobile_id' => 'la cédula o RIF'] as $k => $lbl) { if ($d[$k] === '') { $e[] = "El pago móvil está incompleto: falta $lbl."; } }
            if ($d['mobile_bank'] !== '' && ($len($d['mobile_bank']) < 3 || $len($d['mobile_bank']) > 80 || !preg_match('/^[\p{L}0-9\s\.\-&\'()]+$/u', $d['mobile_bank']))) { $e[] = 'El banco del pago móvil no es válido.'; }
            if ($d['mobile_phone'] !== '' && !preg_match('/^04(12|14|16|24|26|22)-[0-9]{7}$/', $d['mobile_phone'])) { $e[] = 'El teléfono del pago móvil debe tener el formato 0414-1234567 (operadoras 0412, 0414, 0416, 0424, 0426 o 0422).'; }
        }
        return $e;
    }

    /**
     * Guarda los datos y deja constancia en el historial, EN UNA SOLA TRANSACCION y solo si algo realmente cambio (guardar sin
     * cambios no ensucia el historial). El historial conserva los valores ANTERIORES para poder revertir un cambio indebido.
     *
     * @param array $input Entrada cruda del formulario (se normaliza y valida aqui: la validacion NO depende del controlador).
     * @param array $actor ['id'=>int,'name'=>string,'role'=>string]
     * @return array ['ok'=>bool, 'errors'=>string[], 'changed'=>string[]  (etiquetas de los campos que cambiaron)]
     */
    public function save(array $input, array $actor) {
        $new = self::normalize($input);
        $errors = self::validate($new);
        if ($errors) { return ['ok' => false, 'errors' => $errors, 'changed' => []]; }

        try {
            $this->db->beginTransaction();
            $cur = $this->db->query("SELECT * FROM {$this->table} WHERE id = 1 FOR UPDATE")->fetch(PDO::FETCH_ASSOC) ?: [];
            $old = []; $diffKeys = [];
            foreach (array_keys(self::FIELDS) as $k) {
                $o = (string)($cur[$k] ?? ''); $old[$k] = $o;
                if ($o !== $new[$k]) { $diffKeys[] = $k; }
            }
            if (!$diffKeys) { $this->db->rollBack(); return ['ok' => true, 'errors' => [], 'changed' => []]; }

            $sets = []; $vals = [];
            foreach (array_keys(self::FIELDS) as $k) { $sets[] = "$k = ?"; $vals[] = $new[$k] === '' ? null : $new[$k]; }
            $sets[] = 'updated_by = ?'; $vals[] = isset($actor['id']) ? (int)$actor['id'] : null;
            $sets[] = 'updated_by_name = ?'; $vals[] = mb_substr((string)($actor['name'] ?? ''), 0, 100);
            $sets[] = 'updated_at = ?'; $vals[] = date('Y-m-d H:i:s');
            $this->db->prepare("INSERT INTO {$this->table} (id) VALUES (1) ON DUPLICATE KEY UPDATE id = id")->execute();
            $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = 1")->execute($vals);

            $pick = fn(array $src) => array_intersect_key($src, array_flip($diffKeys));
            $this->db->prepare(
                "INSERT INTO school_bank_account_history (changed_by, changed_by_name, changed_by_role, changed_fields, old_values, new_values)
                 VALUES (?, ?, ?, ?, ?, ?)"
            )->execute([
                isset($actor['id']) ? (int)$actor['id'] : null,
                mb_substr((string)($actor['name'] ?? ''), 0, 100), mb_substr((string)($actor['role'] ?? ''), 0, 20),
                implode(', ', array_map(fn($k) => self::FIELDS[$k], $diffKeys)),
                json_encode($pick($old), JSON_UNESCAPED_UNICODE), json_encode($pick($new), JSON_UNESCAPED_UNICODE),
            ]);
            $this->db->commit();
            return ['ok' => true, 'errors' => [], 'changed' => array_map(fn($k) => self::FIELDS[$k], $diffKeys)];
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            error_log('[SchoolBankAccount::save] ' . $e->getMessage());
            return ['ok' => false, 'errors' => ['No se pudieron guardar los datos bancarios. Inténtalo de nuevo.'], 'changed' => []];
        }
    }

    /** Ultimos cambios (mas reciente primero) para la bitacora del administrador. */
    public function recentHistory($limit = 8) {
        $stmt = $this->db->prepare("SELECT * FROM school_bank_account_history ORDER BY id DESC LIMIT :n");
        $stmt->bindValue(':n', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
