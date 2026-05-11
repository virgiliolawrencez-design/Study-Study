<?php
require '../app/config/db.php';

// Array untuk menyimpan history minggu-minggu sebelumnya
$history_data = [];

// Looping dari minggu pertama sampai minggu sebelum current_week
for ($w = 1; $w < $current_week; $w++) {
    // Total challenge di minggu ke-$w
    $t_query = $conn->query("SELECT COUNT(*) as count FROM challenges WHERE week = $w");
    $total = $t_query->fetch_assoc()['count'];

    // Total selesai di minggu ke-$w
    $c_query = $conn->query("
        SELECT COUNT(*) as count FROM user_challenges uc 
        JOIN challenges c ON uc.challenge_id = c.id 
        WHERE c.week = $w AND uc.user_id = $user_id AND uc.status = 'completed'
    ");
    $completed = $c_query->fetch_assoc()['count'];

    $pct = ($total > 0) ? round(($completed / $total) * 100) : 0;
    
    // Masukkan ke array
    $history_data[$w] = $pct;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>StudyTrack - History</title>
    <link rel="stylesheet" href="/css/home.css">
    <style>
        .history-list { display: flex; flex-direction: column; gap: 30px; }
        .history-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 22px;
        }
        .progress-mini-outline {
            width: 100%;
            height: 40px;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 15px;
            overflow: hidden;
            position: relative;
        }
        .progress-mini-fill {
            height: 100%;
            background: linear-gradient(90deg, #ff7f11, #ffb36c);
            transition: width 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #111;
            font-weight: 900;
            font-size: 1.1rem;
        }
        .arrow-icon {
            font-size: 2.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
            color: #ffb36c;
        }
        .arrow-icon:hover { transform: scale(1.2); }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <a href="/" class="logo"><h1 class="logo-text">StudyTrack</h1></a>
        <nav class="menu">
            <a href="/challenge" class="menu-item challenge">Challenge</a>
            <a href="/progress" class="menu-item progress">Progress</a>
            <a href="/history" class="menu-item history active">History</a>
            <a href="/profile" class="menu-item profile">Profile</a>
            <a href="/logout" class="menu-item logout">Logout</a>
        </nav>
        <div class="sidebar-mascot">
            <img src="/assets/Image1.png" alt="Mascot">
        </div>
    </aside>

    <main class="main-content">
        <h2 class="section-title">History Progress</h2>
        <div class="history-list">
            
            <?php if(empty($history_data)): ?>
                <div class="card">
                    <p style="font-size: 1.2rem; color: #fff;">Belum ada history karena ini masih minggu pertama.</p>
                </div>
            <?php else: ?>
                <?php foreach($history_data as $minggu => $persentase): ?>
                <div class="card history-card">
                    <div class="history-info" style="flex:1;">
                        <label>Progress Minggu <?= $minggu ?></label>
                        <div class="progress-mini-outline" style="margin-top: 14px;">
                            <div class="progress-mini-fill" style="width: <?= $persentase ?>%;"><?= $persentase ?>%</div>
                        </div>
                    </div>
                    <div class="arrow-icon">❯</div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
    </main>
</div>
</body>
</html>