<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Session;
use App\Models\Api;
use App\Models\Rating;
use App\Views\View;
use Exception;

final class MovieController
{
    private Api $api;
    private Rating $rating;
    private View $view;

    public function __construct()
    {
        $database = new Database();
        $pdo = $database->getConnection();
        
        $this->api = new Api();
        $this->rating = new Rating($pdo);
        $this->view = new View();
    }

    public function index(): void
    {
        $this->view->render('movie/index', [
            'pageTitle' => 'Movie Search',
            'error' => $_GET['error'] ?? null,
        ]);
    }

    public function search(): void
    {
        // Handle both GET and POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $movieTitle = trim($_POST['title'] ?? '');
        } else {
            // Handle GET request (from popular search links)
            $movieTitle = trim($_GET['title'] ?? '');
        }

        // Following instructor's empty validation
        if (empty($movieTitle)) {
            $this->redirectWithError('empty_title');
            return;
        }

        try {
            $movieData = $this->api->searchMovie($movieTitle);

            if ($movieData['Response'] === 'True') {
                Session::set('current_movie', $movieData);
                $this->view->render('movie/results', [
                    'pageTitle' => 'Movie Results',
                    'movie' => $movieData,
                    'isLoggedIn' => Session::has('user_id'),
                ]);
            } else {
                $this->redirectWithError('not_found');
            }
        } catch (Exception $e) {
            error_log('Movie search error: ' . $e->getMessage());
            $this->redirectWithError('search_failed');
        }
    }

    public function review(string $movieTitle, string $ratingValue): void
    {
        $movieTitle = urldecode($movieTitle);
        $rating = (int) $ratingValue;

        // Following instructor's validation approach
        if ($rating < 1 || $rating > 5 || (string) $rating !== $ratingValue) {
            $this->redirectWithError('invalid_rating');
            return;
        }

        // Following instructor's session-based user tracking
        if (!Session::has('user_id')) {
            header('Location: /login');
            exit;
        }

        $userId = (int) Session::get('user_id');

        try {
            // Save rating (following instructor's three required fields)
            $this->rating->saveRating($userId, $movieTitle, $rating);

            // Generate AI review
            $review = $this->api->generateReview($movieTitle, $rating);

            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'review' => $review,
                'rating' => $rating,
            ]);
        } catch (Exception $e) {
            error_log('Rating submission error: ' . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => 'Failed to submit rating. Please try again.',
            ]);
        }
    }

    private function redirectWithError(string $error): void
    {
        header("Location: /movie?error={$error}");
        exit;
    }
}