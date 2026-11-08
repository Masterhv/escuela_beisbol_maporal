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
     * Inicializa la conexión a la base de datos.
     */
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
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
     * @return int|false El ID del último registro insertado o false en caso de error.
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
            return $this->db->lastInsertId();
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