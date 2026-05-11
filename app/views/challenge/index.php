<?php
// Pastikan session sudah aktif dan db tersedia di index.php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$role = $_SESSION['role'] ?? 'student';
$challenges = $challenges ?? [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tantangan - StudyTrack</title>
    <link rel="stylesheet" href="/css/home.css">
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
            background: #04060d;
        }

        .sidebar {
            width: 320px;
            background-color: #040608;
            border-right: 1px solid rgba(255,255,255,0.08);
            display: flex;
            flex-direction: column;
            padding: 40px 25px;
            position: relative;
        }

        .menu {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin-top: 26px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #ffffff;
            font-weight: 700;
            padding: 18px 24px;
            min-height: 64px;
            width: 100%;
            border: 1px solid #2563eb;
            border-radius: 999px;
            background: #1d4ed8;
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
            font-size: 1.05rem;
            box-shadow: 0 12px 24px rgba(37,99,235,0.12);
        }

        .menu-item.active {
            background: #2563eb;
            border-color: #3b82f6;
            box-shadow: 0 12px 24px rgba(37,99,235,0.22);
        }

        .menu-item:hover {
            transform: translateX(2px);
            background: #2563eb;
        }

        .sidebar-mascot {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-top: auto;
            text-align: center;
            padding: 20px 0 0;
        }

        .sidebar-mascot img {
            width: 180px;
            height: auto;
            max-width: 100%;
            filter: drop-shadow(0 10px 20px rgba(0,0,0,0.25));
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        .sidebar-quote {
            font-size: 0.95rem;
            color: rgba(255,255,255,0.68);
            margin-top: 18px;
            line-height: 1.6;
        }

        .main-content {
            flex: 1;
            padding: 48px;
            background: linear-gradient(180deg, #07101f 0%, #08132a 100%);
            overflow-y: auto;
            position: relative;
        }

        .page-header {
            max-width: 820px;
        }

        .section-title {
            font-size: 3.4rem;
            line-height: 1.02;
            color: #fff;
            margin-bottom: 18px;
        }

        .page-description {
            color: rgba(255,255,255,0.78);
            font-size: 1.05rem;
            max-width: 720px;
            line-height: 1.8;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: 1.3fr 1fr;
            gap: 22px;
            margin-top: 40px;
            align-items: start;
        }

        .card-large {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 30px;
            padding: 30px;
            display: flex;
            gap: 20px;
            align-items: center;
            box-shadow: 0 22px 50px rgba(0,0,0,0.18);
        }

        .icon-circle {
            min-width: 72px;
            min-height: 72px;
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #2d5fff, #74a1ff);
            color: #fff;
            font-size: 1.75rem;
            box-shadow: 0 14px 30px rgba(45,95,255,0.24);
        }

        .card-title {
            color: #fff;
            font-size: 1.15rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .card-text {
            color: rgba(255,255,255,0.72);
            line-height: 1.7;
            font-size: 0.98rem;
        }

        .metric-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .metric-box {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 20px;
            padding: 20px 18px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 12px;
            min-height: auto;
            transition: all 0.25s ease;
        }

        .metric-box:hover {
            border-color: rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.07);
        }

        .metric-details {
            display: flex;
            flex-direction: column;
            gap: 6px;
            width: 100%;
        }

        .metric-title {
            font-size: 0.85rem;
            color: rgba(255,255,255,0.70);
            font-weight: 700;
            line-height: 1.3;
        }

        .metric-value {
            font-size: 1.75rem;
            font-weight: 900;
            color: #fff;
        }

        .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.5rem;
            align-self: flex-end;
            margin-top: 4px;
        }

        .metric-total { background: linear-gradient(135deg, #3b5fff, #7f9dff); }
        .metric-pending { background: linear-gradient(135deg, #f0a60f, #ffcc6d); }
        .metric-completed { background: linear-gradient(135deg, #14b17d, #4ce89d); }

        .empty-state-card {
            margin-top: 42px;
            border: 1px dashed rgba(255,255,255,0.16);
            border-radius: 34px;
            padding: 64px 44px;
            background: rgba(255,255,255,0.03);
            text-align: center;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,0.02);
        }

        .empty-state-card img {
            width: 150px;
            margin: 0 auto 28px;
        }

        .empty-state-card h3 {
            color: #fff;
            font-size: 2rem;
            margin-bottom: 14px;
        }

        .empty-state-card p {
            color: rgba(255,255,255,0.7);
            font-size: 1.05rem;
            line-height: 1.8;
            max-width: 640px;
            margin: 0 auto;
        }

        .tip-bar {
            margin-top: 34px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 18px 24px;
            display: flex;
            align-items: center;
            gap: 14px;
            color: rgba(255,255,255,0.78);
        }

        .tip-icon {
            width: 46px;
            height: 46px;
            border-radius: 16px;
            background: rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #ffd36d;
        }

        @media (max-width: 980px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { width: 100%; }
            .main-content { padding: 32px 24px; }
            .summary-cards { grid-template-columns: 1fr; }
            .metric-strip { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">
                <h1 class="logo-text">StudyTrack</h1>
            </div>
            <nav class="menu">
                <a href="/challenge" class="menu-item challenge active">Challenge</a>
                <a href="/progress" class="menu-item progress">Progress</a>
                <a href="/history" class="menu-item history">History</a>
                <a href="/profile" class="menu-item profile">Profile</a>
                <a href="/logout" class="menu-item logout">Logout</a>
            </nav>
            <div class="sidebar-mascot">
                <img src="/assets/Image1.png" alt="StudyTrack Mascot">
            </div>
        </aside>
        <main class="main-content">
            <div class="page-header">
                <h2 class="section-title">Daftar Tantangan Minggu <?= htmlspecialchars($current_week) ?></h2>
                <p class="page-description"><?php if ($role === 'teacher'): ?>Kelola tantangan tugas Anda atau lihat yang tersedia untuk diselesaikan.<?php else: ?>Lihat tantangan minggu ini dan selesaikan yang belum dikerjakan.<?php endif; ?></p>
            </div>
            <div class="summary-cards">
                <div class="card-large">
                    <div class="icon-circle">📘</div>
                    <div class="card-content">
                        <div class="card-title">Tantangan Minggu Ini</div>
                        <p class="card-text">Selesaikan semua tantangan untuk meningkatkan progres belajarmu!</p>
                    </div>
                </div>
                <div class="metric-strip">
                    <div class="metric-box">
                        <div class="metric-details">
                            <div class="metric-title">Total Tantangan</div>
                            <div class="metric-value"><?= htmlspecialchars($totalChallenges ?? count($challenges)) ?></div>
                        </div>
                        <div class="metric-icon metric-total">🏁</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-details">
                            <div class="metric-title">Belum Dikerjakan</div>
                            <div class="metric-value"><?= htmlspecialchars($pendingChallenges ?? 0) ?></div>
                        </div>
                        <div class="metric-icon metric-pending">⏳</div>
                    </div>
                    <div class="metric-box">
                        <div class="metric-details">
                            <div class="metric-title">Selesai</div>
                            <div class="metric-value"><?= htmlspecialchars($completedChallenges ?? 0) ?></div>
                        </div>
                        <div class="metric-icon metric-completed">✅</div>
                    </div>
                </div>
            </div>
            <div class="empty-state-card">
                <img src="/assets/Image1.png" alt="No tasks">
                <h3>Tidak ada tantangan ditemukan untuk minggu ini.</h3>
                <p>Nikmati waktu belajarmu dan nantikan tantangan berikutnya!</p>
            </div>
            <div class="tip-bar">
                <div class="tip-icon">💡</div>
                <p><strong>Tips:</strong> Buat jadwal belajar rutin dan selesaikan tantangan satu per satu untuk hasil maksimal!</p>
            </div>
            <div class="challenge-grid">
                <?php if (empty($challenges)): ?>
                    <div class="empty-state">
                        <p>Tidak ada tantangan ditemukan untuk minggu ini.</p>
                        <?php if ($role === 'teacher'): ?>
                            <p><a href="/challenge/create" style="color: #EAE0CF; text-decoration: underline;">Buat tantangan pertama Anda!</a></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php foreach ($challenges as $challenge): ?>
                    <div class="challenge-card <?php if (($challenge['status'] ?? null) === 'completed') echo 'completed'; ?>">
                        <h3><?= htmlspecialchars($challenge['title']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($challenge['description'])) ?></p>
                        <div class="challenge-meta">
                            <span class="badge">Kategori: <?= htmlspecialchars($challenge['category'] ?: 'Umum') ?></span>
                            <span class="badge">Kesulitan: <?= htmlspecialchars($challenge['difficulty'] ?: 'Medium') ?></span>
                            <span class="badge">Minggu: <?= htmlspecialchars($challenge['week']) ?></span>
                            <span class="badge">Dibuat oleh: <?= htmlspecialchars($challenge['created_by_name'] ?? 'Admin') ?></span>
                        </div>
                        <div class="challenge-actions">
                            <?php if ($role === 'teacher'): ?>
                                <a href="/challenge/edit/<?= intval($challenge['id']) ?>" class="btn btn-secondary">Edit</a>
                                <form method="POST" action="/challenge/delete/<?= intval($challenge['id']) ?>" style="display:inline;" onsubmit="return confirm('Hapus tantangan ini?');">
                                    <button type="submit" class="btn btn-secondary">Hapus</button>
                                </form>
                            <?php else: ?>
                                <?php if (($challenge['status'] ?? null) === 'completed'): ?>
                                    <button class="btn btn-done" disabled>Sudah Selesai ✓</button>
                                <?php else: ?>
                                    <form method="POST" action="/challenge/complete/<?= intval($challenge['id']) ?>" style="display:inline;">
                                        <button type="submit" class="btn btn-primary">Selesaikan</button>
                                    </form>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>
