<?php 
use App\Core\Session;
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Login</h2>
            </div>
            <div class="card-body">
                <?php if (Session::has('_flash') && isset($_SESSION['_flash']['success'])): ?>
                    <div class="alert alert-success">
                        <?= Session::getFlash('success', '') ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url('/login') ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" 
                               class="form-control" 
                               id="email" 
                               name="email" 
                               value="<?= htmlspecialchars($email ?? '') ?>"
                               required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" 
                               class="form-control" 
                               id="password" 
                               name="password" 
                               required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <p class="mb-0">
                        Don't have an account? 
                        <a href="<?= url('/register') ?>">Register here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
