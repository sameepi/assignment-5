<?php

declare(strict_types=1);

namespace App\Models;

use Exception;

final class Api
{
    private string $omdbKey;
    private string $geminiKey;

    public function __construct()
    {
        // Following instructor's environment variable approach
        $this->omdbKey = $_ENV['OMDB_API_KEY'] ?? throw new Exception('OMDB API key not found');
        $this->geminiKey = $_ENV['GEMINI_API_KEY'] ?? throw new Exception('Gemini API key not found');
    }

    public function searchMovie(string $movieTitle): array
    {
        $url = 'http://www.omdbapi.com/?t=' . urlencode($movieTitle) . '&apikey=' . $this->omdbKey;

        $response = file_get_contents($url);
        if ($response === false) {
            throw new Exception('Failed to fetch movie data from OMDB API');
        }

        $data = json_decode($response, true);
        if ($data === null) {
            throw new Exception('Invalid JSON response from OMDB API');
        }

        return $data;
    }

    public function generateReview(string $movieName, int $rating): string
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->geminiKey;

        // Following instructor's prompt structure
        $prompt = "Please give a review of the movie '{$movieName}' from someone who rated it {$rating} out of five. Make it personal and detailed.";

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
        ];

        // Following instructor's curl approach
        $jsonData = json_encode($data);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError !== '') {
            throw new Exception('Curl error: ' . $curlError);
        }

        if ($httpCode !== 200) {
            throw new Exception("HTTP error: {$httpCode}");
        }

        $result = json_decode($response, true);
        if ($result === null) {
            throw new Exception('Invalid JSON response from Gemini API');
        }

        // Following instructor's deep array access approach
        return $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Review generation failed';
    }
}