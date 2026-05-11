<?php
// session_start(); // Already started in index.php

// Jika user sudah login, langsung arahkan ke profile
if (isset($_SESSION['user_id'])) {
    header("Location: /profile");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Study Track</title>
    <link rel="stylesheet" href="/css/register.css">
</head>
<body>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">MASUK</h1>
            <p class="hero-subtitle">Belajar bahasa secara gratis. Selalu.</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" style="grid-area: subtitle; margin-top: 30px; color: #ff6b6b;"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/login" class="auth-form mx-auto max-w-md">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-full">Masuk</button>
            </form>
            
            <div class="auth-links">
                <p>Belum punya akun? <a href="/register" class="link">Daftar sekarang</a></p>
            </div>
        </div>
        <div class="hero-image">
            <div class="duo-mascot"><img src="/assets/Image1.png" alt="Duo Mascot"></div>
        </div>
    </div>
</section>

</body>
</html>