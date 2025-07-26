
document.addEventListener('DOMContentLoaded', function() {
    initializeMovieSearch();
    initializeRatingSystem();
    initializeTooltips();
});

function initializeMovieSearch() {
    const searchForm = document.getElementById('movieSearchForm');
    if (!searchForm) return;

    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const searchButton = searchForm.querySelector('button[type="submit"]');
        const spinner = searchForm.querySelector('.spinner-border');
        const searchInput = searchForm.querySelector('input[name="title"]');
        
        // Validate input
        if (!searchInput.value.trim()) {
            showAlert('Please enter a movie title', 'danger');
            searchInput.focus();
            return;
        }
        
        // Show loading state
        searchButton.disabled = true;
        if (spinner) spinner.classList.remove('d-none');
        
        // Submit the form
        this.submit();
        
        // Re-enable button after timeout (in case of error)
        setTimeout(() => {
            searchButton.disabled = false;
            if (spinner) spinner.classList.add('d-none');
        }, 10000);
    });
}

function initializeRatingSystem() {
    document.addEventListener('click', function(e) {
        // Handle star rating clicks
        if (e.target.closest('.rating-stars')) {
            const starsContainer = e.target.closest('.rating-stars');
            const stars = Array.from(starsContainer.querySelectorAll('.star'));
            const clickedStar = e.target.closest('.star');
            
            if (!clickedStar) return;
            
            const rating = parseInt(clickedStar.getAttribute('data-rating') || '0');
            if (!rating) return;
            
            // Update visual state
            updateStarRating(starsContainer, rating);
            
            // If this is the main rating section, submit the rating
            if (starsContainer.closest('#ratingSection')) {
                submitMovieRating(rating);
            }
        }
    });
    
    // Hover effects for stars
    document.addEventListener('mouseover', function(e) {
        if (e.target.closest('.rating-stars') && e.target.classList.contains('star')) {
            const starsContainer = e.target.closest('.rating-stars');
            const stars = Array.from(starsContainer.querySelectorAll('.star'));
            const hoveredStar = e.target.closest('.star');
            
            if (!hoveredStar) return;
            
            const rating = parseInt(hoveredStar.getAttribute('data-rating') || '0');
            if (!rating) return;
            
            // Update visual state
            updateStarRating(starsContainer, rating, true);
        }
    });
    
    // Reset stars on mouseout if no rating is selected
    document.addEventListener('mouseout', function(e) {
        if (e.target.closest('.rating-stars')) {
            const starsContainer = e.target.closest('.rating-stars');
            const currentRating = parseInt(starsContainer.getAttribute('data-current-rating') || '0');
            
            if (currentRating === 0) {
                const stars = starsContainer.querySelectorAll('.star');
                stars.forEach(star => {
                    star.classList.remove('text-warning');
                    star.classList.add('text-muted');
                });
            }
        }
    });
}

function updateStarRating(container, rating, isHover = false) {
    const stars = container.querySelectorAll('.star');
    
    stars.forEach((star, index) => {
        if (index < rating) {
            star.classList.add('text-warning');
            star.classList.remove('text-muted');
        } else {
            star.classList.remove('text-warning');
            star.classList.add('text-muted');
        }
    });
    
    if (!isHover) {
        container.setAttribute('data-current-rating', rating);
    }
}

async function submitMovieRating(rating) {
    const movieTitle = document.querySelector('h1')?.textContent || 'this movie';
    const messageContainer = document.getElementById('ratingMessage');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    if (!messageContainer) return;
    
    // Show loading state
    messageContainer.innerHTML = `
        <div class="alert alert-info d-flex align-items-center">
            <div class="spinner-border spinner-border-sm me-2" role="status"></div>
            <span>Submitting your rating...</span>
        </div>
    `;
    
    try {
        // Use the url() helper to generate the correct URL
        const response = await fetch(url(`/movie/review/${encodeURIComponent(movieTitle)}/${rating}`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ _token: csrfToken })
        });

        const data = await response.json();
        
        if (!response.ok) {
            throw new Error(data.error || 'Failed to submit rating');
        }

        // Show success message
        messageContainer.innerHTML = `
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill me-2"></i>
                Thanks! You rated ${movieTitle} ${rating} out of 5 stars.
            </div>
        `;
        
        // Show AI review section if it exists and we have a review
        const aiReviewSection = document.getElementById('aiReviewSection');
        if (aiReviewSection && data.review) {
            aiReviewSection.classList.remove('d-none');
            aiReviewSection.scrollIntoView({ behavior: 'smooth' });
            
            // Display the AI review
            const reviewContent = document.getElementById('aiReviewContent');
            if (reviewContent) {
                reviewContent.textContent = data.review;
            }
        }
    } catch (error) {
        console.error('Error submitting rating:', error);
        messageContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${error.message || 'Failed to submit rating. Please try again.'}
            </div>
        `;
    }
}

function generateAIReview(movieTitle, rating) {
    const reviewContainer = document.getElementById('aiReviewContent');
    if (!reviewContainer) return;
    
    // Show loading state
    reviewContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Generating your personalized review...</p>
        </div>
    `;
    
    // Simulate API call delay
    setTimeout(() => {
        const review = generateMockAIReview(movieTitle, rating);
        displayTypedReview(review, reviewContainer);
    }, 1500);
}

function generateMockAIReview(movieTitle, rating) {
    const positiveAspects = [
        `The cinematography in "${movieTitle}" was absolutely stunning, with each frame carefully composed to create a visually immersive experience.`,
        `The performances in "${movieTitle}" were outstanding, with the cast delivering powerful and believable portrayals that drew me into the story.`,
        `The soundtrack of "${movieTitle}" perfectly complemented the film's tone, enhancing key emotional moments and adding depth to the storytelling.`
    ];
    
    const negativeAspects = [
        `While "${movieTitle}" had its moments, the pacing felt uneven, with some scenes dragging on longer than necessary.`,
        `The plot of "${movieTitle}" contained several predictable elements that made the story feel somewhat formulaic and lacking in surprises.`,
        `Some of the character development in "${movieTitle}" felt rushed, making it difficult to fully connect with their journeys.`
    ];
    
    const conclusions = [
        `Overall, "${movieTitle}" is a ${rating >= 4 ? 'must-see' : rating >= 3 ? 'solid' : 'disappointing'} film that ${rating >= 4 ? 'exceeds expectations' : rating >= 3 ? 'has its moments' : 'fails to deliver'} on multiple fronts.`,
        `In conclusion, I would ${rating >= 4 ? 'highly recommend' : rating >= 3 ? 'recommend' : 'not recommend'} "${movieTitle}" to ${rating >= 4 ? 'anyone looking for an exceptional' : rating >= 3 ? 'fans of the genre, though it may not appeal to everyone' : 'most viewers, as it falls short in several key areas'}.`,
        `All things considered, "${movieTitle}" is a ${rating >= 4 ? 'remarkable' : rating >= 3 ? 'decent' : 'lackluster'} film that ${rating >= 4 ? 'leaves a lasting impression' : rating >= 3 ? 'has its strengths and weaknesses' : 'ultimately disappoints'} despite its potential.`
    ];
    
    // Build the review based on the rating
    let review = `After watching "${movieTitle}" and giving it ${rating} out of 5 stars, I have to say that `;
    
    // Add positive aspects (more for higher ratings)
    const positiveCount = Math.min(Math.ceil(rating / 2), 2);
    for (let i = 0; i < positiveCount; i++) {
        const randomIndex = Math.floor(Math.random() * positiveAspects.length);
        review += positiveAspects[randomIndex] + ' ';
        // Remove used aspect to avoid repetition
        positiveAspects.splice(randomIndex, 1);
    }
    
    // Add negative aspects (more for lower ratings)
    if (rating <= 3) {
        const negativeCount = Math.max(1, 4 - rating);
        for (let i = 0; i < negativeCount && negativeAspects.length > 0; i++) {
            const randomIndex = Math.floor(Math.random() * negativeAspects.length);
            review += negativeAspects[randomIndex] + ' ';
            // Remove used aspect to avoid repetition
            negativeAspects.splice(randomIndex, 1);
        }
    }
    
    // Add conclusion
    const conclusionIndex = Math.floor(Math.random() * conclusions.length);
    review += conclusions[conclusionIndex];
    
    return review;
}

function displayTypedReview(text, element) {
    let i = 0;
    const speed = 10; // milliseconds per character
    
    element.innerHTML = '<p class="mb-0"></p>';
    const paragraph = element.querySelector('p');
    
    function typeWriter() {
        if (i < text.length) {
            paragraph.textContent += text.charAt(i);
            i++;
            setTimeout(typeWriter, speed);
            
            // Auto-scroll to keep the latest text visible
            element.scrollTop = element.scrollHeight;
        }
    }
    
    typeWriter();
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Show an alert message
 * @param {string} message - The message to display
 * @param {string} type - The alert type (success, danger, warning, info)
 */
function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to the top of the main content
    const main = document.querySelector('main');
    if (main.firstChild) {
        main.insertBefore(alertDiv, main.firstChild);
    } else {
        main.appendChild(alertDiv);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = bootstrap.Alert.getOrCreateInstance(alertDiv);
        alert.close();
    }, 5000);
}

// Make functions available globally
window.MovieApp = {
    showAlert,
    submitMovieRating,
    generateAIReview
}