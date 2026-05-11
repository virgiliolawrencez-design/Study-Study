<?php
// session_start(); // Already started in index.php
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Study Track</title>
    <link rel="stylesheet" href="/css/register.css">
</head>
<body>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">DAFTAR</h1>
            <p class="hero-subtitle">Buat akun gratis dan mulai belajar sekarang.</p>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="/register" class="auth-form mx-auto max-w-md">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required class="form-control">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required class="form-control">
                        <option value="" disabled selected>Pilih Role</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <input type="password" id="password" name="password" required minlength="6" class="form-control">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Kata Sandi</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="6" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary w-full">Daftar Gratis</button>
            </form>
            
            <div class="auth-links">
                <p>Sudah punya akun? <a href="/login" class="link">Masuk sekarang</a></p>
            </div>
        </div>
        <div class="hero-image">
            <div class="duo-mascot"><img src="/assets/Image1.png" alt="Duo Mascot"></div>
        </div>
    </div>
</section>

</body>
</html>
