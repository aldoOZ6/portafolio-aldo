<?php
namespace App\Helpers;

class DB {
    public static function get() {
        static $pdo = null;

        if ($pdo === null) {
            $host = 'localhost';        // servidor de base de datos
            $port = 3307;               // puerto correcto de tu MySQL
            $db   = 'ecommerce';        // nombre de tu base de datos
            $user = 'root';             // usuario que sí funciona
            $pass = '';                 // sin contraseña
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            try {
                $pdo = new \PDO($dsn, $user, $pass, [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (\PDOException $e) {
                die('Error al conectar con la base de datos: ' . $e->getMessage());
            }
        }

        return $pdo;
    }
}
