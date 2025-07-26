<?php

declare(strict_types=1);

use App\Core\Session;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Session::get('_token', '') ?>">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Movie Rating App'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('/css/app.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include __DIR__ . '/../components/header.php'; ?>
    
    <main class="container py-4">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php echo $content ?? ''; ?>
    </main>

    <?php include __DIR__ . '/../components/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= url('/js/app.js') ?>"></script>
    <script>
        // Make the url() helper available globally
        function url(path = '') {
            const baseUrl = '<?= rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/') ?>';
            path = path.startsWith('/') ? path.substring(1) : path;
            return baseUrl + (path ? '/' + path : '');
        }
    </script>
</body>
</html>