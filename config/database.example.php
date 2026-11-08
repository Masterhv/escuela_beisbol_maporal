<?php
/**
 * PLANTILLA de conexion a la base de datos. Copiala como config/database.php y completa los datos del servidor.
 * config/database.php NO se versiona (esta en .gitignore): contiene la clave de la base de datos.
 *
 * EN PRODUCCION no uses el usuario 'root'. Crea un usuario dedicado solo para esta base, con una clave larga y aleatoria:
 *   CREATE USER 'maporal_app'@'localhost' IDENTIFIED BY '<clave aleatoria>';
 *   GRANT SELECT, INSERT, UPDATE, DELETE ON escuela_beisbol_maporal.* TO 'maporal_app'@'localhost';
 *   FLUSH PRIVILEGES;
 * (El sistema no necesita CREATE/DROP/ALTER en el dia a dia; las migraciones se hacen con otra cuenta.)
 */
/**
 * Clase Database para manejar la conexión a la base de datos.
 * Utiliza PDO para una conexión segura y compatible con múltiples SGBD.
 */
class Database {
    // --- Credenciales de Conexión a MySQL ---
    
    /**
     * @var string El host del servidor de la base de datos.
     */
    private $host = '127.0.0.1'; // o la dirección de tu servidor MySQL

    /**
     * @var string El nombre de la base de datos.
     */
    private $db_name = 'escuela_beisbol_maporal'; // nombre de tu base de datos MySQL

    /**
     * @var string El nombre de usuario para la conexión a la base de datos.
     */
    private $username = 'USUARIO_DE_LA_BASE_DE_DATOS'; // tu usuario de MySQL

    /**
     * @var string La contraseña para la conexión a la base de datos.
     */
    private $password = 'CLAVE_DE_LA_BASE_DE_DATOS'; // tu contraseña de MySQL

    /**
     * @var string El puerto del servidor de la base de datos.
     */
    private $port = '3306'; // Puerto por defecto de MySQL

    /**
     * @var PDO|null La instancia de la conexión PDO.
     */
    public $conn;

    /**
     * Obtiene la conexión a la base de datos.
     * Si ya existe una conexión, la devuelve. Si no, crea una nueva.
     *
     * @return PDO|null La instancia de la conexión PDO o null si falla la conexión.
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            // DSN (Data Source Name) para la conexión PDO
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8";
            
            // Crea una nueva instancia de PDO
            $this->conn = new PDO($dsn, $this->username, $this->password);
            
            // Configura los atributos de PDO para un manejo de errores robusto y un fetch consistente
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Lanza excepciones en caso de error
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // Desactiva la emulación de preparaciones para mayor seguridad
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // Establece que los resultados se devuelvan como arrays asociativos por defecto
        } catch(PDOException $exception) {
            // Muestra un mensaje de error si la conexión falla
            echo "Error de conexión: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}