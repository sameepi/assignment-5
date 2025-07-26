<?php
/** @var array $movie */
/** @var bool $isLoggedIn */
/** @var string|null $error */
?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <img src="<?php echo !empty($movie['Poster']) && $movie['Poster'] !== 'N/A' 
                ? htmlspecialchars($movie['Poster']) 
                : 'https://via.placeholder.com/300x450/cccccc/666666?text=No+Poster'; ?>" 
                 class="card-img-top" 
                 alt="<?php echo htmlspecialchars($movie['Title']); ?>"
                 style="max-height: 500px; object-fit: cover;">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($movie['Title']); ?> (<?php echo htmlspecialchars($movie['Year']); ?>)</h5>
                <p class="card-text">
                    <small class="text-muted">
                        <?php echo htmlspecialchars($movie['Rated'] ?? 'N/A'); ?> | 
                        <?php echo htmlspecialchars($movie['Runtime'] ?? 'N/A'); ?>
                    </small>
                </p>
                <div class="d-flex align-items-center mb-2">
                    <div class="rating-stars me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star <?php echo $i <= floor($movie['imdbRating'] / 2) ? 'text-warning' : 'text-muted'; ?>">★</span>
                        <?php endfor; ?>
                    </div>
                    <span class="badge bg-primary">
                        <?php echo number_format($movie['imdbRating'] ?? 0, 1); ?>/10
                    </span>
                </div>
                <?php if (isset($movie['Metascore']) && $movie['Metascore'] !== 'N/A'): ?>
                    <div class="mb-2">
                        <span class="badge bg-success">Metascore: <?php echo $movie['Metascore']; ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h3 class="h5 mb-0">Movie Details</h3>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Director:</strong> <?php echo htmlspecialchars($movie['Director'] ?? 'N/A'); ?></p>
                        <p><strong>Writer:</strong> <?php echo htmlspecialchars($movie['Writer'] ?? 'N/A'); ?></p>
                        <p><strong>Actors:</strong> <?php echo htmlspecialchars($movie['Actors'] ?? 'N/A'); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Genre:</strong> <?php echo htmlspecialchars($movie['Genre'] ?? 'N/A'); ?></p>
                        <p><strong>Release Date:</strong> <?php echo htmlspecialchars($movie['Released'] ?? 'N/A'); ?></p>
                        <p><strong>Box Office:</strong> <?php echo htmlspecialchars($movie['BoxOffice'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <h5 class="mt-4">Plot</h5>
                <p><?php echo htmlspecialchars($movie['Plot'] ?? 'No plot available.'); ?></p>
                
                <?php if (!empty($movie['Awards']) && $movie['Awards'] !== 'N/A'): ?>
                    <h5 class="mt-4">Awards</h5>
                    <p><?php echo htmlspecialchars($movie['Awards']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Rating Section -->
        <div class="card" id="ratingSection">
            <div class="card-header bg-success text-white">
                <h3 class="h5 mb-0">Rate This Movie</h3>
            </div>
            <div class="card-body">
                <?php if ($isLoggedIn): ?>
                    <div class="text-center">
                        <p class="lead">How would you rate this movie?</p>
                        <div class="rating-stars mb-3" style="font-size: 2rem;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star" 
                                      data-rating="<?php echo $i; ?>" 
                                      onclick="rateMovie(<?php echo $i; ?>)">
                                    ★
                                </span>
                            <?php endfor; ?>
                        </div>
                        <div id="ratingMessage" class="mt-2"></div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        <p class="mb-2">Please <a href="/login">login</a> or <a href="/register">register</a> to rate this movie and get AI-generated reviews!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- AI Review Section (Initially hidden) -->
        <div class="card mt-4 d-none" id="aiReviewSection">
            <div class="card-header bg-info text-white">
                <h3 class="h5 mb-0">🤖 AI-Generated Review</h3>
            </div>
            <div class="card-body">
                <div id="aiReviewContent" class="p-3 bg-light rounded">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 mb-0">Generating your personalized review...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentRating = 0;
const movieTitle = '<?php echo addslashes($movie['Title']); ?>';

function rateMovie(rating) {
    if (currentRating === rating) return;
    
    currentRating = rating;
    updateStarDisplay();
    
    // Show loading state
    const messageDiv = document.getElementById('ratingMessage');
    messageDiv.innerHTML = `
        <div class="d-flex align-items-center justify-content-center">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            <span>Submitting your rating...</span>
        </div>
    `;
    
    // Send rating to server
    fetch('/movie/rate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            movie: movieTitle,
            rating: rating
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = `
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> 
                    ${data.message || 'Rating submitted successfully!'}
                </div>
            `;
            
            // Show AI review section
            const aiReviewSection = document.getElementById('aiReviewSection');
            aiReviewSection.classList.remove('d-none');
            
            // Scroll to review section
            setTimeout(() => {
                aiReviewSection.scrollIntoView({ behavior: 'smooth' });
                
                // Simulate AI review generation (in a real app, this would be an API call)
                setTimeout(() => {
                    generateAIReview(movieTitle, rating);
                }, 1000);
            }, 500);
            
        } else {
            throw new Error(data.error || 'Failed to submit rating');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        messageDiv.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                Error: ${error.message || 'Failed to submit rating. Please try again.'}
            </div>
        `;
        currentRating = 0;
        updateStarDisplay();
    });
}

function updateStarDisplay() {
    const stars = document.querySelectorAll('.rating-stars .star');
    stars.forEach((star, index) => {
        if (index < currentRating) {
            star.classList.add('text-warning');
            star.classList.remove('text-muted');
        } else {
            star.classList.remove('text-warning');
            star.classList.add('text-muted');
        }
    });
}

function generateAIReview(movieTitle, rating) {
    const reviewContent = document.getElementById('aiReviewContent');
    
    // In a real app, this would be an API call to your backend
    // which would then call the Gemini API
    const fakeAIResponses = [
        `After watching "${movieTitle}" and giving it ${rating} stars, I have to say this film ${rating >= 4 ? 'exceeded my expectations' : 'was ' + (rating >= 3 ? 'decent but had room for improvement' : 'disappointing')}. `,
        `The ${rating >= 4 ? 'exceptional' : rating >= 3 ? 'solid' : 'lackluster'} ${rating >= 4 ? 'performance' : 'effort'} in "${movieTitle}" made it a ${rating >= 4 ? 'memorable' : 'forgettable'} experience. `,
        `I gave "${movieTitle}" ${rating} stars because ${rating >= 4 ? 'it was an outstanding film that I would definitely recommend' : rating >= 3 ? 'while it had its moments, it didn\'t quite meet my expectations' : 'unfortunately, it failed to impress me in several areas'}. `
    ];
    
    const review = fakeAIResponses[Math.floor(Math.random() * fakeAIResponses.length)];
    const additionalThoughts = [
        "The cinematography was particularly striking, with each frame carefully composed to enhance the storytelling.",
        "The character development was well-executed, making me truly invested in their journeys.",
        "The pacing kept me engaged throughout, with just the right balance of action and character moments.",
        "While the plot had some predictable elements, the strong performances more than made up for it.",
        "The film's soundtrack perfectly complemented the mood and enhanced the emotional impact of key scenes."
    ];
    
    // Add some random additional thoughts
    for (let i = 0; i < 2; i++) {
        const randomIndex = Math.floor(Math.random() * additionalThoughts.length);
        if (!review.includes(additionalThoughts[randomIndex])) {
            review += additionalThoughts[randomIndex] + ' ';
        }
    }
    
    // Final recommendation
    review += `Overall, I would ${rating >= 4 ? 'highly recommend' : rating >= 3 ? 'recommend giving it a watch' : 'suggest skipping this one unless you\'re a die-hard fan of the genre'}.`;
    
    // Simulate typing effect
    let i = 0;
    const speed = 20; // milliseconds per character
    
    reviewContent.innerHTML = '';
    
    function typeWriter() {
        if (i < review.length) {
            reviewContent.innerHTML += review.charAt(i);
            i++;
            setTimeout(typeWriter, speed);
        }
    }
    
    typeWriter();
}

// Initialize star hover effects
document.querySelectorAll('.rating-stars .star').forEach(star => {
    star.addEventListener('mouseover', function() {
        const rating = parseInt(this.getAttribute('data-rating') || '0');
        highlightStars(rating);
    });
    
    star.addEventListener('mouseout', () => {
        if (!currentRating) {
            document.querySelectorAll('.rating-stars .star').forEach(s => {
                s.classList.remove('text-warning');
                s.classList.add('text-muted');
            });
        } else {
            updateStarDisplay();
        }
    });
});

function highlightStars(rating) {
    document.querySelectorAll('.rating-stars .star').forEach((star, index) => {
        if (index < rating) {
            star.classList.add('text-warning');
            star.classList.remove('text-muted');
        } else {
            star.classList.remove('text-warning');
            star.classList.add('text-muted');
        }
    });
}
</script>