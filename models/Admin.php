<?php
require_once 'models/Model.php';

/**
 * Modelo Admin para gestionar los administradores del sistema.
 * Hereda de la clase Model y define la lógica de negocio para los administradores.
 */
class Admin extends Model {
    /**
     * @var string La tabla de la base de datos para los administradores.
     */
    protected $table = 'admins';
    
    /**
     * Autentica a un administrador.
     *
     * @param string $username El nombre de usuario.
     * @param string $password La contraseña.
     * @return array|false Los datos del administrador si la autenticación es exitosa, de lo contrario false.
     */
    public function authenticate($username, $password) {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar que el usuario exista y la contraseña sea correcta
        if ($admin && password_verify($password, $admin['password'])) {
            // Actualizar la fecha del último login
            $this->update($admin['id'], ['last_login' => date('Y-m-d H:i:s')]);
            return $admin;
        }

        return false;
    }

    /**
     * Crea un nuevo administrador.
     *
     * @param array $data Los datos del administrador a crear.
     * @return int|false El ID del nuevo administrador o false si falla.
     */
    public function createAdmin($data) {
        $data['created_date'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }
    
    /**
     * Actualiza la contraseña de un administrador.
     *
     * @param int $id El ID del administrador.
     * @param string $newPassword La nueva contraseña hasheada.
     * @return bool True si se actualizó correctamente, false si no.
     */
    public function updatePassword($id, $newPassword) {
        return $this->update($id, ['password' => $newPassword]);
    }
    
    /**
     * Obtiene estadísticas sobre los administradores.
     *
     * @return array Un array con el total de administradores, los activos y los administradores principales.
     */
    public function getAdminStats() {
        $sql = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN role = 'administrador' THEN 1 ELSE 0 END) as superadmins
                FROM {$this->table}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Busca administradores por nombre completo, nombre de usuario o email.
     * La búsqueda es insensible a mayúsculas y minúsculas.
     *
     * @param string $term El término de búsqueda.
     * @return array Una lista de administradores que coinciden con el término.
     */
    public function searchAdmins($term) {
        $sql = "SELECT id, full_name, username, email, role
                FROM {$this->table}
                WHERE LOWER(full_name) LIKE LOWER(:term)
                   OR LOWER(username) LIKE LOWER(:term)
                   OR LOWER(email) LIKE LOWER(:term)
                ORDER BY full_name
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':term', "%{$term}%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca administradores por número de cédula.
     * Proporciona autocompletado basado en la cédula.
     *
     * @param string $term El término de búsqueda (cédula).
     * @return array Una lista de administradores que coinciden con la cédula.
     */
    public function searchByIdCard($term) {
        $sql = "SELECT id, full_name, username, email, role, id_card
                FROM {$this->table}
                WHERE id_card LIKE :term
                ORDER BY full_name
                LIMIT 10";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':term', "%{$term}%");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un nombre de usuario ya está en uso.
     *
     * @param string $username El nombre de usuario a verificar.
     * @param int|null $excludeId Un ID de administrador a excluir de la búsqueda (útil al actualizar).
     * @return bool True si el nombre de usuario ya existe, false si no.
     */
    public function isUsernameTaken($username, $excludeId = null) {
        $sql = "SELECT id FROM {$this->table} WHERE username = :username";
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':username', $username);
        if ($excludeId !== null) {
            $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica si un número de cédula ya está en uso.
     *
     * @param string $id_card El número de cédula a verificar.
     * @param int|null $excludeId Un ID de administrador a excluir de la búsqueda.
     * @return bool True si la cédula ya existe, false si no.
     */
    /**
     * Busca un administrador por su cedula (acceso por vinculo representante-atletia).
     *
     * @param string $idCard Cedula a buscar.
     * @return array|null Fila del administrador o null si no existe.
     */
    public function findByIdCard($idCard) {
        $sql = "SELECT * FROM {$this->table} WHERE id_card = :id_card LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_card', $idCard);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function isIdCardTaken($id_card, $excludeId = null) {
        if (empty($id_card)) {
            return false;
        }
        $sql = "SELECT id FROM {$this->table} WHERE id_card = :id_card";
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id_card', $id_card);
        if ($excludeId !== null) {
            $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Verifica si un email ya está en uso.
     *
     * @param string $email El email a verificar.
     * @param int|null $excludeId Un ID de administrador a excluir de la búsqueda (útil al actualizar).
     * @return bool True si el email ya existe, false si no.
     */
    public function isEmailTaken($email, $excludeId = null) {
        if (empty($email)) {
            return false;
        }
        $sql = "SELECT id FROM {$this->table} WHERE email = :email";
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        if ($excludeId !== null) {
            $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Busca un administrador por su dirección de correo electrónico.
     *
     * @param string $email El email a buscar.
     * @return array|false Los datos del administrador o false si no se encuentra.
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND status = 'active'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Establece un token de restablecimiento de contraseña para un administrador.
     *
     * @param int $id El ID del administrador.
     * @param string $token El token a establecer.
     * @return bool True si la operación fue exitosa, false si no.
     */
    public function setPasswordResetToken($id, $token) {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
        return $this->update($id, [
            'reset_token' => $token,
            'reset_token_expires_at' => $expiresAt
        ]);
    }

    /**
     * Busca un administrador por su token de restablecimiento de contraseña.
     * El token debe ser válido y no haber expirado.
     *
     * @param string $token El token a buscar.
     * @return array|false Los datos del administrador o false si no se encuentra.
     */
    public function findByPasswordResetToken($token) {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token AND reset_token_expires_at > NOW()";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Limpia el token de restablecimiento de contraseña de un administrador.
     * Se usa después de que la contraseña ha sido actualizada correctamente.
     *
     * @param int $id El ID del administrador.
     * @return bool True si la operación fue exitosa, false si no.
     */
    public function clearPasswordResetToken($id) {
        return $this->update($id, [
            'reset_token' => null,
            'reset_token_expires_at' => null
        ]);
    }

    /**
     * Verifica la clave de operaciones especiales del administrador actualmente en sesión.
     *
     * @param int $adminId El ID del administrador.
     * @param string $key La clave de 4 dígitos a verificar.
     * @return bool True si la clave es correcta, false en caso contrario.
     */
    /**
     * Indica si el administrador ya registro una clave de operaciones especiales.
     *
     * @param int $adminId ID del administrador.
     * @return bool True si tiene clave registrada (no vacia).
     */
    public function hasOperationKey($adminId) {
        $sql = "SELECT operation_key FROM {$this->table} WHERE id = :id AND role = 'administrador'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($row['operation_key']);
    }

    public function verifyOperationKey($adminId, $key) {
        $sql = "SELECT operation_key FROM {$this->table} WHERE id = :id AND role = 'administrador'";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['operation_key'])) {
            return false;
        }

        return password_verify($key, $row['operation_key']);
    }
}