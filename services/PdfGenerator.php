<?php
namespace App\Core\Traits;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

/**
 * Trait PdfGenerator
 * Proporciona la funcionalidad para generar documentos PDF a partir de HTML,
 * con código QR de verificación de integridad embebido en cada documento.
 */
trait PdfGenerator {

    /**
     * Genera y envía un archivo PDF al navegador.
     *
     * @param string $html         El contenido HTML que se convertirá en PDF.
     * @param string $filename     El nombre del archivo PDF de salida (sin extensión).
     * @param string $paper        Tamaño del papel ('A4', 'letter', etc.).
     * @param string $orientation  Orientación ('portrait' o 'landscape').
     * @param array  $verification Datos de verificación: ['token', 'fingerprint', 'verifyUrl']
     */
    protected function generatePdf($html, $filename, $paper = 'A4', $orientation = 'portrait', $verification = []) {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        $dompdf->stream($filename . '.pdf', ['Attachment' => false]);
        exit();
    }

    /**
     * Genera un PDF a partir de HTML y devuelve su contenido binario en lugar
     * de enviarlo al navegador. Útil para adjuntar el PDF a integraciones
     * externas (ej. envío por WhatsApp) sin interrumpir la ejecución con exit().
     *
     * @param string $html         El contenido HTML que se convertirá en PDF.
     * @param string $paper        Tamaño del papel ('A4', 'letter', etc.).
     * @param string $orientation  Orientación ('portrait' o 'landscape').
     * @return string El contenido binario del PDF generado.
     */
    protected function generatePdfString($html, $paper = 'A4', $orientation = 'portrait') {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($paper, $orientation);
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Envía un PDF ya generado al navegador forzando su descarga (no se abre en el visor).
     * Termina la ejecución con exit().
     *
     * @param string $pdfContent Contenido binario del PDF.
     * @param string $filename   Nombre del archivo, con o sin extensión .pdf.
     */
    protected function downloadPdfString($pdfContent, $filename) {
        // Nombre seguro: solo letras, números, guion y guion bajo.
        $base = preg_replace('/\.pdf$/i', '', (string)$filename);
        $base = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
        $base = preg_replace('/_+/', '_', $base);
        $base = trim($base, '_') ?: 'documento';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $base . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        echo $pdfContent;
        exit();
    }

    /**
     * Renderiza una vista de PDF, genera el registro de verificación con QR,
     * e inyecta el QR + token en el layout antes de retornar el HTML final.
     *
     * @param string $view   Nombre de la vista PDF (sin extensión).
     * @param array  $data   Datos disponibles en la vista.
     * @param array  $verificationPayload  Datos clave del reporte para fingerprint.
     * @param string $reportType  Tipo de reporte para el registro.
     * @param string $filename    Nombre del archivo para el registro.
     * @param bool   $withVerification Si es false, no se registra verificación ni se incluye QR
     *                                 (útil para documentos en blanco sin datos que verificar).
     * @return string HTML completo del PDF con QR embebido.
     */
    protected function renderPdfView($view, $data = [], $verificationPayload = [], $reportType = 'report', $filename = 'reporte', $withVerification = true) {
        require_once 'models/PdfVerification.php';
        require_once 'vendor/autoload.php';

        if ($withVerification) {
            // --- 1. Registrar verificación y obtener token ---
            $verifyModel    = new \PdfVerification();
            $adminId        = $_SESSION['admin_id']   ?? null;
            $adminName      = $_SESSION['admin_name'] ?? 'Sistema';

            // Añadir metadatos al payload
            $verificationPayload['generated_at'] = date('d/m/Y H:i:s');
            $verificationPayload['generated_by'] = $adminName;
            $verificationPayload['filename']     = $filename . '.pdf';
            $verificationPayload['report_type']  = $reportType;

            $reg   = $verifyModel->register($reportType, $filename . '.pdf', $verificationPayload, $adminId, $adminName);
            $token = $reg['token'];
            $fingerprint = $reg['fingerprint'];

            // --- 2. Construir URL de verificación ---
            $verifyUrl = rtrim(BASE_URL, '/') . '/verify?token=' . $token;

            // --- 3. Generar QR como PNG base64 ---
            $qrBase64 = $this->generateQrBase64($verifyUrl);

            // --- 4. Datos de verificación inyectados en la vista ---
            $data['_qr_base64']    = $qrBase64;
            $data['_verify_url']   = $verifyUrl;
            $data['_token']        = $token;
            $data['_fingerprint']  = substr($fingerprint, 0, 16) . '...';
            // Datos de creacion del documento que se muestran en el PDF
            $data['_generated_by'] = $adminName;
            $data['_generated_at'] = $verificationPayload['generated_at'];
            $data['_report_type']  = $reportType;
            $data['_generated_file'] = $verificationPayload['filename'];
        }

        extract($data);

        ob_start();
        // 'require' (no 'require_once') para poder generar más de un PDF en la misma petición.
        require 'views/reports/pdf/layouts/main.php';
        return ob_get_clean();
    }

    /**
     * Genera un código QR como string base64 (PNG) para incrustar en el PDF.
     *
     * @param string $content  El contenido del QR (URL de verificación).
     * @return string  Data URI base64, ej: "data:image/png;base64,..."
     */
    private function generateQrBase64($content) {
        try {
            $qrCode = new QrCode(
                data: $content,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 200,
                margin: 6,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
                foregroundColor: new Color(139, 0, 0),   // Rojo oscuro corporativo
                backgroundColor: new Color(255, 255, 255)
            );

            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $png    = $result->getString();

            return 'data:image/png;base64,' . base64_encode($png);
        } catch (\Exception $e) {
            // Si falla la generación del QR, retornar vacío (el PDF se genera igual)
            return '';
        }
    }
}
?>
