<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private $db;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function register(string $first_name, string $last_name, string $email, string $password): bool
    {
        $query = "INSERT INTO users (first_name, last_name, email, password, created_at, updated_at) 
                 VALUES (:first_name, :last_name, :email, :password, NOW(), NOW())";
        
        $stmt = $this->db->prepare($query);
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        return $stmt->execute([
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':email' => $email,
            ':password' => $hashed_password
        ]);
    }

    public function login(string $email, string $password): ?array
    {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']); // Don't return the password
            return $user;
        }

        return null;
    }

    public function findByEmail(string $email): ?array
    {
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }
}
