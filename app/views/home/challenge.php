<?php
// Halaman ini dibuat sebagai redirect agar tombol/menu "Tantangan" mengarah ke halaman tantangan yang benar.
// Sumber data & kontrol penyelesaian dikelola oleh ChallengeController (route: /challenge).
if (!isset($_SESSION)) {
    // jika session belum aktif, index.php biasanya sudah start; tapi amankan:
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

header('Location: /challenge');
exit;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>StudyTrack - Challenge</title>
    <link rel="stylesheet" href="/css/home.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <a href="/" class="logo"><h1 class="logo-text">StudyTrack</h1></a>
            <nav class="menu">
                <a href="challenge.php" class="menu-item challenge active">Challenge</a>
                <a href="progress.php" class="menu-item progress">Progress</a>
                <a href="history.php" class="menu-item history">History</a>
                <a href="profile.php" class="menu-item profile">Profile</a>
            </nav>
            <div class="sidebar-mascot">
                <img src="/assets/Image1.png" alt="StudyTrack Mascot">
            </div>
        </aside>
        
        <main class="main-content">
            <h2 class="section-title">Tantangan Minggu <?= $current_week ?></h2>
            <div class="challenge-list">
                
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="challenge-card">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <span class="category"><?= htmlspecialchars($row['category']) ?></span>
                    <br>
                    
                    <?php if($row['status'] == 'completed'): ?>
                        <button class="btn btn-done" disabled>Sudah Selesai ✓</button>
                    <?php else: ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="challenge_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="complete_challenge" class="btn btn-complete">Selesaikan</button>
                        </form>
                    <?php endif; ?>
                </div>
                <?php endwhile; ?>

            </div>
        </main>
    </div>
</body>
</html>