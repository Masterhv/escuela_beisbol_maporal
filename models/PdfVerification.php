<?php
require_once 'models/Model.php';

/**
 * Modelo para registrar y consultar verificaciones de PDFs generados.
 * Cada PDF exportado genera un token único y un fingerprint (HMAC-SHA256)
 * de los datos clave, permitiendo validar que el documento no fue alterado.
 */
class PdfVerification extends Model {

    protected $table = 'pdf_verifications';

    /**
     * Clave con la que se firmaron los documentos emitidos ANTES de sacar el secreto del código. Se conserva solo como
     * clave de verificación de respaldo: esos QR siguen validándose. NO se usa para firmar documentos nuevos.
     */
    const LEGACY_HMAC_KEY = 'maporal_pdf_secret_2026';

    /**
     * Clave con la que se FIRMAN los documentos nuevos: 'hmac_key' de config/app.php. Si falta (instalación sin
     * configurar), se cae en la clave heredada para no dejar de emitir documentos, y se avisa en el log.
     */
    private static function signingKey() {
        $k = function_exists('app_config') ? (string)app_config('hmac_key', '') : '';
        if (strlen($k) < 32) {
            error_log('[PdfVerification] config/app.php no define hmac_key (>= 32 caracteres): se firma con la clave heredada. Configurala antes de producción.');
            return self::LEGACY_HMAC_KEY;
        }
        return $k;
    }

    /** Claves aceptadas para VERIFICAR: la actual, las anteriores de config/app.php y la heredada (documentos ya emitidos). */
    private static function verificationKeys() {
        $keys = [self::signingKey()];
        $prev = function_exists('app_config') ? app_config('hmac_previous_keys', []) : [];
        foreach ((array)$prev as $k) { if (is_string($k) && $k !== '') { $keys[] = $k; } }
        $keys[] = self::LEGACY_HMAC_KEY;
        return array_values(array_unique($keys));
    }

    /**
     * Registra un nuevo PDF generado y devuelve el token único.
     *
     * @param string $reportType  Tipo de reporte (athlete_card, athlete_list, etc.)
     * @param string $filename    Nombre del archivo PDF
     * @param array  $payload     Datos clave del reporte (se serializan como JSON)
     * @param int|null $generatedBy   ID del admin que generó el PDF
     * @param string|null $generatedByName Nombre del admin
     * @return array ['token' => string, 'fingerprint' => string]
     */
    public function register($reportType, $filename, array $payload, $generatedBy = null, $generatedByName = null) {
        // Token único de 32 bytes (64 hex chars)
        $token = bin2hex(random_bytes(32));

        // Serializar payload de forma determinista
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

        // HMAC-SHA256 del payload con clave secreta
        $fingerprint = hash_hmac('sha256', $payloadJson, self::signingKey());

        $this->create([
            'token'             => $token,
            'report_type'       => $reportType,
            'filename'          => $filename,
            'generated_by'      => $generatedBy,
            'generated_by_name' => $generatedByName,
            'fingerprint'       => $fingerprint,
            'payload'           => $payloadJson,
            'generated_at'      => date('Y-m-d H:i:s'),
        ]);

        return ['token' => $token, 'fingerprint' => $fingerprint];
    }

    /**
     * Busca un registro por token.
     *
     * @param string $token
     * @return array|false
     */
    public function findByToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE token = :token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica que el fingerprint almacenado coincide con el payload registrado.
     * Incrementa el contador de verificaciones.
     *
     * @param string $token
     * @return array  ['valid' => bool, 'record' => array|null, 'payload' => array|null]
     */
    public function verify($token) {
        $record = $this->findByToken($token);

        if (!$record) {
            return ['valid' => false, 'record' => null, 'payload' => null];
        }

        // Re-calcular fingerprint para detectar manipulación de la BD
        // Un documento es autentico si su huella coincide con CUALQUIERA de las claves aceptadas (la actual o una anterior).
        $valid = false;
        foreach (self::verificationKeys() as $key) {
            if (hash_equals(hash_hmac('sha256', $record['payload'], $key), $record['fingerprint'])) { $valid = true; break; }
        }

        // Registrar la verificación
        $this->update($record['id'], [
            'verified_count'   => $record['verified_count'] + 1,
            'last_verified_at' => date('Y-m-d H:i:s'),
        ]);

        $payload = json_decode($record['payload'], true);

        return [
            'valid'   => $valid,
            'record'  => $record,
            'payload' => $payload,
        ];
    }
}
