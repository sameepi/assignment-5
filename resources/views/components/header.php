<?php
use App\Core\Session;
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo url('/'); ?>">🎬 Movie Rating App</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/'); ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo url('/movie'); ?>">Search Movies</a>
                </li>
            </ul>
            <ul class="navbar-nav">
                <?php if (Session::has('user_id')): ?>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars(Session::get('username')); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('/logout'); ?>">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('/login'); ?>">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo url('/register'); ?>">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>