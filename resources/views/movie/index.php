<?php
/** @var string|null $error */
?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Search for Movies</h2>
            </div>
            <div class="card-body">
                <form action="<?= url('/movie/search') ?>" method="post" id="movieSearchForm">
                    <div class="mb-3">
                        <label for="movieTitle" class="form-label">Movie Title</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="movieTitle" 
                               name="title" 
                               placeholder="Enter movie title (e.g., Inception, The Dark Knight)" 
                               required>
                        <div class="form-text">Search for any movie to rate and review</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <span class="spinner-border spinner-border-sm d-none" id="searchSpinner" role="status" aria-hidden="true"></span>
                            Search Movies
                        </button>
                    </div>
                </form>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mt-3">
                        <?php 
                        switch($error) {
                            case 'not_found':
                                echo 'No movies found. Please try a different title.';
                                break;
                            case 'api_error':
                                echo 'There was an error connecting to the movie database. Please try again later.';
                                break;
                            default:
                                echo '' . htmlspecialchars($error);
                        }
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-4">
                    <h5 class="text-muted">Popular Searches:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= url('/movie/search?title=Barbie') ?>" class="btn btn-outline-secondary btn-sm">Barbie</a>
                        <a href="<?= url('/movie/search?title=Oppenheimer') ?>" class="btn btn-outline-secondary btn-sm">Oppenheimer</a>
                        <a href="<?= url('/movie/search?title=Inception') ?>" class="btn btn-outline-secondary btn-sm">Inception</a>
                        <a href="<?= url('/movie/search?title=The+Dark+Knight') ?>" class="btn btn-outline-secondary btn-sm">The Dark Knight</a>
                        <a href="<?= url('/movie/search?title=Parasite') ?>" class="btn btn-outline-secondary btn-sm">Parasite</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('movieSearchForm').addEventListener('submit', function(e) {
    const spinner = document.getElementById('searchSpinner');
    const button = this.querySelector('button[type="submit"]');
    
    // Disable button and show spinner
    button.disabled = true;
    spinner.classList.remove('d-none');
    
    // Re-enable after 5 seconds in case of error
    setTimeout(() => {
        button.disabled = false;
        spinner.classList.add('d-none');
    }, 5000);
});
</script>