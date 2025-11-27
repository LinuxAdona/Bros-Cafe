<?php

// Create connection
class Database
{
    private const ENVIRONMENT = 'production';
    private $host = 'localhost';
    private $user = self::ENVIRONMENT === 'production' ? 'broscafe_sys' : 'root';
    private $pass = self::ENVIRONMENT === 'production' ? '-Ski;c5)nQL9' : '';
    private $dbname = 'broscafe_db';
    private $conn;
    private $error;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->dbname,
                $this->user,
                $this->pass,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                )
            );
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Connection Error: ' . $this->error;
        }
    }

    public function getConnection()
    {
        return $this->conn;
    }
}
