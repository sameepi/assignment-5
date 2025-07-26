<?php

declare(strict_types=1);

namespace App\Models;

use Exception;
use PDO;

final class Rating
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function saveRating(int $userId, string $movieName, int $rating): bool
    {
        // Following instructor's validation requirements
        if ($rating < 1 || $rating > 5) {
            throw new Exception('Invalid rating. Must be whole number between 1-5.');
        }

        if (empty(trim($movieName))) {
            throw new Exception('Movie name cannot be empty');
        }

        $sql = 'INSERT INTO ratings (user_id, movie_name, rating, created_at) 
                VALUES (?, ?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE rating = ?, updated_at = NOW()';

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$userId, $movieName, $rating, $rating]);
    }

    public function getUserRating(int $userId, string $movieName): ?int
    {
        $sql = 'SELECT rating FROM ratings WHERE user_id = ? AND movie_name = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $movieName]);
        
        $result = $stmt->fetchColumn();
        return $result !== false ? (int) $result : null;
    }

    public function getAverageRating(string $movieName): ?float
    {
        $sql = 'SELECT AVG(rating) as avg_rating FROM ratings WHERE movie_name = ?';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$movieName]);
        
        $result = $stmt->fetchColumn();
        return $result !== null ? (float) $result : null;
    }
}