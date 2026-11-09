<?php
/**
 * Clase base para los modelos.
 * Proporciona una capa de abstracción para interactuar con la base de datos.
 * Incluye métodos CRUD (Crear, Leer, Actualizar, Eliminar) básicos.
 */
class Model {
    /**
     * @var PDO La instancia de la conexión a la base de datos.
     */
    protected $db;

    /**
     * @var string El nombre de la tabla de la base de datos asociada al modelo.
     */
    protected $table;
    
    /**
     * Constructor de la clase Model.
     * Inicializa la conexión a la base de datos de manera tolerante a fallos.
     */
    public function __construct() {
        if (!class_exists('Database')) {
            $base = defined('BASE_PATH') ? BASE_PATH : (dirname(__DIR__) . DIRECTORY_SEPARATOR);
            $dbConfig = $base . 'config' . DIRECTORY_SEPARATOR . 'database.php';
            $dbConfigAlt = $base . 'config' . DIRECTORY_SEPARATOR . 'Database.php';

            if (is_file($dbConfig)) {
                require_once $dbConfig;
            } elseif (is_file($dbConfigAlt)) {
                require_once $dbConfigAlt;
            }
        }

        if (class_exists('Database')) {
            try {
                $database = new Database();
                $this->db = $database->getConnection();
            } catch (\Throwable $e) {
                error_log('[Model] Error al conectar con la base de datos: ' . $e->getMessage());
                $this->db = null;
            }
        } else {
            error_log('[Model] No se encontró la clase Database. Asegúrate de que config/database.php existe en el servidor.');
            $this->db = null;
        }
    }

    /**
     * Devuelve la instancia de conexión PDO activa o null si no se pudo conectar.
     *
     * @return PDO|null
     */
    public function getDb() {
        return $this->db;
    }
    
    /**
     * Busca y devuelve todos los registros que coinciden con las condiciones dadas.
     *
     * @param array $conditions Condiciones para la cláusula WHERE (ej. ['username = :username' => 'valor']).
     * @param string $orderBy Cláusula para ordenar los resultados.
     * @param int|null $limit Número máximo de registros a devolver.
     * @return array Un array de registros.
     */
    public function findAll($conditions = [], $orderBy = 'id DESC', $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', array_keys($conditions));
        }
        
        $sql .= " ORDER BY {$orderBy}";
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                // Extraer el nombre del parámetro de la clave (ej. de 'username = :username' a 'username')
                preg_match('/:(\w+)/', $key, $matches);
                if (isset($matches[1])) {
                    $param = $matches[1];
                    $stmt->bindValue(":{$param}", $value);
                }
            }
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Busca un registro por su ID.
     *
     * @param int $id El ID del registro a buscar.
     * @return array|false El registro encontrado o false si no se encuentra.
     */
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea un nuevo registro en la base de datos.
     *
     * @param array $data Un array asociativo con los datos a insertar (columna => valor).
     * @return int|true|false El ID del registro insertado, true si la fila se insertó sin id
     *                        generado (tabla sin AUTO_INCREMENT) o false en caso de error.
     *                        Nunca devuelve 0: el INSERT exitoso debe ser siempre verdad (truthy),
     *                        para que el controlador no muestre un falso "error al crear" con la fila ya creada.
     */
    public function create($data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        if ($stmt->execute()) {
            $id = (int) $this->db->lastInsertId();
            return $id > 0 ? $id : true;
        }
        return false;
    }
    
    /**
     * Actualiza un registro existente en la base de datos.
     *
     * @param int $id El ID del registro a actualizar.
     * @param array $data Un array asociativo con los datos a actualizar (columna => valor).
     * @return bool True si la actualización fue exitosa, false en caso contrario.
     */
    public function update($id, $data) {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "{$key} = :{$key}";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        $stmt->bindParam(':id', $id);
        foreach ($data as $key => $value) {
            $stmt->bindValue(":{$key}", $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Elimina un registro de la base de datos.
     *
     * @param int $id El ID del registro a eliminar.
     * @return bool True si la eliminación fue exitosa, false en caso contrario.
     */
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}