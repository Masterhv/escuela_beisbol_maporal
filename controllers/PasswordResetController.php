<?php

require_once 'models/Admin.php';
require_once 'services/Mailer.php';

/**
 * Controlador para el proceso de restablecimiento de contraseña.
 * Maneja la solicitud, el envío del enlace y la actualización de la contraseña.
 */
class PasswordResetController extends Controller {

    /**
     * Constructor de la clase.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Muestra el formulario para solicitar el restablecimiento de contraseña.
     */
    public function request() {
        $this->view->render('auth/password_request', ['title' => 'Restablecer Contraseña']);
    }

    /**
     * Procesa la solicitud de restablecimiento.
     * Genera un token, lo guarda en la base de datos y envía un correo con el enlace.
     */
    public function sendLink() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email']);
            
            // Validar formato de email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->view->render('auth/password_request', [
                    'error' => 'Por favor ingrese un correo electrónico válido.',
                    'title' => 'Restablecer Contraseña',
                    'email' => $email
                ]);
                return;
            }
            
            $adminModel = new Admin();
            $admin = $adminModel->findByEmail($email);

            if ($admin) {
                $token = bin2hex(random_bytes(50));
                $adminModel->setPasswordResetToken($admin['id'], $token);

                $resetLink = BASE_URL . 'password/reset/' . $token;
                
                $mailer = new Mailer();
                $subject = 'Restablecimiento de Contraseña - Sistema de Gestión';
                $body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                        <h2 style='color: #8B1538;'>Restablecimiento de Contraseña</h2>
                        <p>Hola <strong>" . htmlspecialchars($admin['full_name']) . "</strong>,</p>
                        <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.</p>
                        <p>Para crear una nueva contraseña, haz clic en el siguiente botón:</p>
                        <div style='text-align: center; margin: 30px 0;'>
                            <a href='{$resetLink}' style='background-color: #8B1538; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block;'>Restablecer Contraseña</a>
                        </div>
                        <p>O copia y pega este enlace en tu navegador:</p>
                        <p style='background-color: #f5f5f5; padding: 10px; border-radius: 5px; word-break: break-all;'>{$resetLink}</p>
                        <p style='color: #666; font-size: 12px; margin-top: 30px;'>Este enlace expirará en 1 hora.</p>
                        <p style='color: #666; font-size: 12px;'>Si no solicitaste este cambio, puedes ignorar este correo de forma segura.</p>
                    </div>
                ";
                
                // 'password_reset' esta EXENTO del limite diario de correos: si una averia agotara el cupo, nadie podria recuperar su acceso.
                if ($mailer->send($email, $subject, $body, SendLimiter::PURPOSE_PASSWORD_RESET)) {
                    $this->view->render('auth/password_link_sent', ['title' => 'Enlace Enviado', 'email' => $email]);
                } else {
                    $this->view->render('auth/password_request', [
                        'error' => 'No se pudo enviar el correo. Por favor verifica tu conexión e inténtalo de nuevo.',
                        'title' => 'Restablecer Contraseña',
                        'email' => $email
                    ]);
                }
            } else {
                // Mostrar error si el correo no está registrado
                $this->view->render('auth/password_request', [
                    'error' => 'El correo electrónico no está registrado en el sistema.',
                    'title' => 'Restablecer Contraseña',
                    'email' => $email
                ]);
            }
        } else {
            $this->redirect('password/request');
        }
    }

    /**
     * Muestra el formulario para ingresar la nueva contraseña.
     * Valida el token proporcionado en la URL.
     *
     * @param string $token El token de restablecimiento.
     */
    public function reset($token) {
        $adminModel = new Admin();
        $admin = $adminModel->findByPasswordResetToken($token);

        if (!$admin) {
            // Token inválido o expirado, redirige al login con un mensaje.
            $this->redirect('auth/login?error=invalid_token');
        }

        $this->view->render('auth/password_reset', [
            'token' => $token,
            'title' => 'Crear Nueva Contraseña'
        ]);
    }

    /**
     * Procesa la actualización de la contraseña.
     * Valida el token y las nuevas contraseñas, luego actualiza en la BD.
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['token'];
            $password = $_POST['password'];
            $passwordConfirm = $_POST['password_confirm'];

            // Validar que las contraseñas no estén vacías y coincidan
            if (empty($password) || $password !== $passwordConfirm) {
                $this->view->render('auth/password_reset', [
                    'token' => $token,
                    'error' => 'Las contraseñas no coinciden o están vacías.',
                    'title' => 'Crear Nueva Contraseña'
                ]);
                return;
            }

            // Validar requisitos de seguridad de la contraseña
            $errors = [];
            if (strlen($password) < 8) {
                $errors[] = 'La contraseña debe tener mínimo 8 caracteres';
            }
            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = 'La contraseña debe contener al menos una letra mayúscula';
            }
            if (!preg_match('/[a-z]/', $password)) {
                $errors[] = 'La contraseña debe contener al menos una letra minúscula';
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = 'La contraseña debe contener al menos un número';
            }
            if (!preg_match('/[!@#$%^&*]/', $password)) {
                $errors[] = 'La contraseña debe contener al menos un símbolo (!@#$%^&*)';
            }

            if (!empty($errors)) {
                $this->view->render('auth/password_reset', [
                    'token' => $token,
                    'error' => implode('. ', $errors) . '.',
                    'title' => 'Crear Nueva Contraseña'
                ]);
                return;
            }

            $adminModel = new Admin();
            $admin = $adminModel->findByPasswordResetToken($token);

            if (!$admin) {
                $this->view->render('auth/password_reset', [
                    'token' => $token,
                    'error' => 'El token es inválido o ha expirado. Por favor solicita un nuevo enlace de restablecimiento.',
                    'title' => 'Crear Nueva Contraseña'
                ]);
                return;
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $adminModel->updatePassword($admin['id'], $hashedPassword);
            $adminModel->clearPasswordResetToken($admin['id']);

            header('Location: ' . BASE_URL . 'auth/login?status=password_updated');
            exit;
        }
    }
}