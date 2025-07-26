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

    public function generateReview(string $movieName, int $rating, int $timeout = 10): string
    {
        // Input validation
        $rating = max(1, min(5, (int)$rating)); // Ensure rating is between 1-5
        $timeout = max(1, min(30, (int)$timeout)); // Ensure timeout is between 1-30 seconds

        try {
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $this->geminiKey;

            // Create a more detailed prompt
            $prompt = "Write a detailed, personal review of the movie '{$movieName}' from someone who rated it {$rating} out of 5. " .
                     "The review should be 2-3 paragraphs long and discuss the plot, acting, and overall impression. " .
                     "The tone should be " . $this->getToneForRating($rating) . ".";

            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topP' => 0.9,
                    'topK' => 40,
                    'maxOutputTokens' => 1024,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_NONE',
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_NONE',
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_NONE',
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_NONE',
                    ],
                ],
            ];

            $jsonData = json_encode($data);
            if ($jsonData === false) {
                throw new Exception('Failed to encode request data');
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $jsonData,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                ],
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 5, // Connection timeout in seconds
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ]);

            // Execute the request with a timeout
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);

            // Check for cURL errors
            if ($curlError !== '' || $curlErrno !== 0) {
                error_log("Gemini API cURL error ({$curlErrno}): {$curlError}");
                return $this->generateFallbackReview($movieName, $rating);
            }

            // Check HTTP status code
            if ($httpCode !== 200) {
                error_log("Gemini API returned HTTP {$httpCode}");
                return $this->generateFallbackReview($movieName, $rating);
            }

            // Parse response
            $result = json_decode($response, true);
            if ($result === null) {
                error_log('Invalid JSON response from Gemini API');
                return $this->generateFallbackReview($movieName, $rating);
            }

            // Extract the generated text
            $review = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            
            if (empty($review)) {
                error_log('Empty or invalid response from Gemini API');
                return $this->generateFallbackReview($movieName, $rating);
            }

            return $review;

        } catch (Exception $e) {
            error_log('Error generating review with Gemini API: ' . $e->getMessage());
            return $this->generateFallbackReview($movieName, $rating);
        }
    }

    /**
     * Generate a fallback review when the API fails
     */
    private function generateFallbackReview(string $movieName, int $rating): string
    {
        // First, escape the movie name to be safe for HTML output
        $escapedMovieName = htmlspecialchars($movieName, ENT_QUOTES, 'UTF-8');
        
        // Create template parts with proper escaping
        $templates = [
            "I found '{$escapedMovieName}' to be " . $this->getAdjectiveForRating($rating) . ".",
            "The movie had some " . ($rating > 3 ? 'great' : 'interesting') . " moments.",
            "Overall, I'd rate it {$rating} out of 5.",
            "The acting was " . $this->getRandomAspect('acting', $rating) . ".",
            "The plot was " . $this->getRandomAspect('plot', $rating) . ".",
            "The cinematography was " . $this->getRandomAspect('cinematography', $rating) . ".",
        ];
        
        // Shuffle and take 3-4 random aspects
        shuffle($templates);
        $selected = array_slice($templates, 0, rand(3, 4));
        
        // Add a closing statement
        $closing = [
            "I would " . ($rating > 3 ? 'definitely recommend' : 'recommend') . " this movie to " . 
            ($rating > 3 ? 'everyone' : 'fans of the genre') . ".",
            "It's worth watching if you're in the mood for a " . $this->getMoodForRating($rating) . " movie.",
            "My final thoughts: " . $this->getFinalThoughts($rating) . "."
        ];
        
        $selected[] = $closing[array_rand($closing)];
        
        // Join with actual newlines and convert to HTML line breaks
        $review = implode("\n\n", $selected);
        
        // Convert newlines to <br> tags (no need for htmlspecialchars since we've already escaped the content)
        return nl2br($review);
    }

    private function getToneForRating(int $rating): string
    {
        $tones = [
            1 => 'very negative and disappointed',
            2 => 'somewhat negative',
            3 => 'neutral and balanced',
            4 => 'positive and enthusiastic',
            5 => 'extremely positive and excited'
        ];
        return $tones[$rating] ?? 'neutral';
    }
    
    private function getAdjectiveForRating(int $rating): string
    {
        $adjectives = [
            1 => ['terrible', 'awful', 'disappointing', 'poor'],
            2 => ['below average', 'mediocre', 'uninspired', 'lacking'],
            3 => ['average', 'decent', 'okay', 'watchable'],
            4 => ['good', 'enjoyable', 'well-made', 'entertaining'],
            5 => ['excellent', 'outstanding', 'amazing', 'exceptional']
        ];
        $list = $adjectives[$rating] ?? $adjectives[3];
        return $list[array_rand($list)];
    }
    
    private function getRandomAspect(string $aspect, int $rating): string
    {
        $evaluations = [
            'acting' => [
                1 => ['wooden', 'unconvincing', 'amateurish'],
                2 => ['uneven', 'uninspired', 'forgettable'],
                3 => ['competent', 'serviceable', 'adequate'],
                4 => ['strong', 'compelling', 'engaging'],
                5 => ['exceptional', 'award-worthy', 'brilliant']
            ],
            'plot' => [
                1 => ['confusing', 'nonsensical', 'poorly written'],
                2 => ['predictable', 'formulaic', 'uninspired'],
                3 => ['standard', 'straightforward', 'typical'],
                4 => ['engaging', 'well-crafted', 'thoughtful'],
                5 => ['brilliant', 'masterful', 'exceptional']
            ],
            'cinematography' => [
                1 => ['poor', 'amateurish', 'uninspired'],
                2 => ['unremarkable', 'standard', 'forgettable'],
                3 => ['competent', 'well-done', 'solid'],
                4 => ['beautiful', 'striking', 'visually impressive'],
                5 => ['stunning', 'breathtaking', 'visually masterful']
            ]
        ];
        
        $aspectData = $evaluations[$aspect] ?? $evaluations['plot'];
        $list = $aspectData[$rating] ?? $aspectData[3];
        return $list[array_rand($list)];
    }
    
    private function getMoodForRating(int $rating): string
    {
        $moods = [
            1 => 'so bad it\'s funny',
            2 => 'mindless',
            3 => 'casual',
            4 => 'engaging',
            5 => 'must-see'
        ];
        return $moods[$rating] ?? 'casual';
    }
    
    private function getFinalThoughts(int $rating): string
    {
        $thoughts = [
            1 => 'I wouldn\'t recommend this to my worst enemy',
            2 => 'there are probably better ways to spend your time',
            3 => 'it has its moments but nothing special',
            4 => 'it\'s definitely worth your time',
            5 => 'it\'s a must-watch that I\'ll be recommending to everyone'
        ];
        return $thoughts[$rating] ?? 'it was an okay experience';
    }
}