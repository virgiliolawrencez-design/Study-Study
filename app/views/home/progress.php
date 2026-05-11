<?php
// session_start(); // Already started in index.php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>StudyTrack - Progress</title>
    <link rel="stylesheet" href="/css/home.css">
    <style>
        .progress-container {
            max-width: 760px;
            margin: 0 auto;
        }

        .progress-title {
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 30px;
            display: block;
            color: #fff;
            text-align: left;
        }

        .progress-stats {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 30px;
            font-size: 1.1rem;
            font-weight: 700;
            color: rgba(255,255,255,0.85);
            flex-wrap: wrap;
        }

        .progress-bar-outline {
            width: 100%;
            height: 60px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.18);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 25px;
            position: relative;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff7f11, #ffb36c);
            transition: width 1.5s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111;
            font-weight: 900;
            font-size: 1.2rem;
            position: relative;
            overflow: hidden;
        }

        .progress-bar-fill::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { left: -100%; }
            100% { left: 100%; }
        }

        .progress-text {
            text-align: left;
            font-size: 1.1rem;
            color: rgba(255,255,255,0.78);
            margin-top: 20px;
        }

        .motivation-text {
            text-align: left;
            font-size: 1.3rem;
            font-weight: 700;
            color: #ffb36c;
            margin-top: 30px;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <a href="/" class="logo"><h1 class="logo-text">StudyTrack</h1></a>
        <nav class="menu">
            <a href="/challenge" class="menu-item challenge">Challenge</a>
            <a href="/progress" class="menu-item progress active">Progress</a>
            <a href="/history" class="menu-item history">History</a>
            <a href="/profile" class="menu-item profile">Profile</a>
            <a href="/logout" class="menu-item logout">Logout</a>
        </nav>
        <div class="sidebar-mascot">
            <img src="/assets/Image1.png" alt="Mascot">
        </div>
    </aside>

    <main class="main-content">
        <div class="progress-container">
            <span class="progress-title">Progress Minggu <?= htmlspecialchars($current_week) ?></span>

            <div class="progress-stats">
                <span>Selesai: <?= htmlspecialchars($completed_challenges) ?> / <?= htmlspecialchars($total_challenges) ?></span>
                <span><?= htmlspecialchars($progress_percentage) ?>%</span>
            </div>

            <div class="progress-bar-outline">
                <div class="progress-bar-fill" style="width: <?= htmlspecialchars($progress_percentage) ?>%;">
                    <?= htmlspecialchars($progress_percentage) ?>%
                </div>
            </div>

            <div class="progress-text">
                <?php if ($progress_percentage == 100): ?>
                    🎉 Selamat! Anda telah menyelesaikan semua tantangan minggu ini!
                <?php elseif ($progress_percentage >= 75): ?>
                    Hampir selesai! Teruskan kerja bagusnya!
                <?php elseif ($progress_percentage >= 50): ?>
                    Bagus! Anda sudah setengah jalan.
                <?php elseif ($progress_percentage > 0): ?>
                    Mulai bagus! Lanjutkan tantangan berikutnya.
                <?php else: ?>
                    Belum ada tantangan yang diselesaikan. Mulai dari tantangan pertama!
                <?php endif; ?>
            </div>

            <div class="motivation-text">
                "Konsistensi adalah kunci kesuksesan. Tetap semangat!"
            </div>
        </div>
    </main>
</div>
</body>
</html>