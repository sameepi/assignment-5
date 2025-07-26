<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database {
    private $host = 'hfo7d.h.filess.io';
    private $dbname = 'user_teamsithay';
    private $user = 'user_teamsithay';
    private $pass = 'e22e39738d979ef82d9bad5fb17354d8f6fa0941';
    protected $pdo;

    public function __construct() {
        try {
            $dsn = "mysql:host={$this->host};port=3305;dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Add MariaDB specific options if needed
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4' COLLATE 'utf8mb4_unicode_ci'"
            ];
            
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
            
        } catch(PDOException $e) {
            // More detailed error information
            $error = "Connection failed: " . $e->getMessage() . "\n";
            $error .= "Trying to connect to: mysql:host={$this->host};dbname={$this->dbname}\n";
            $error .= "Username: {$this->user}\n";
            die($error);
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}