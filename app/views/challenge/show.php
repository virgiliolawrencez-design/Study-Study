<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$role = $_SESSION['role'] ?? 'student';
$completed = isset($_GET['completed']) && $_GET['completed'] == '1';
$error = isset($_GET['error']) ? $_GET['error'] : '';
$questions = $questions ?? [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Tantangan - StudyTrack</title>
    <link rel="stylesheet" href="/css/home.css">
    <style>
        .dashboard-container { display: flex; min-height: 100vh; background: #04060d; }
        .sidebar { width: 320px; background-color: #040608; border-right: 1px solid rgba(255,255,255,0.08); display: flex; flex-direction: column; padding: 40px 25px; }
        .menu { display: flex; flex-direction: column; gap: 14px; margin-top: 26px; }
        .menu-item { display: flex; align-items: center; justify-content: center; text-decoration: none; color: #ffffff; font-weight: 700; padding: 18px 24px; min-height: 64px; width: 100%; border: 1px solid #2563eb; border-radius: 999px; background: #1d4ed8; transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease; font-size: 1.05rem; box-shadow: 0 12px 24px rgba(37,99,235,0.12); }
        .menu-item.active { background: #2563eb; border-color: #3b82f6; box-shadow: 0 12px 24px rgba(37,99,235,0.22); }
        .menu-item:hover { transform: translateX(2px); background: #2563eb; }
        .sidebar-mascot { display: flex; flex-direction: column; align-items: center; justify-content: center; margin-top: auto; text-align: center; padding: 20px 0 0; }
        .sidebar-mascot img { width: 180px; height: auto; max-width: 100%; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.25)); animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-10px); } }

        .main-content { flex: 1; padding: 48px; background: linear-gradient(180deg, #07101f 0%, #08132a 100%); overflow-y: auto; }
        .page-header { max-width: 820px; }
        .section-title { font-size: 3.2rem; line-height: 1.02; color: #fff; margin-bottom: 12px; }
        .page-description { color: rgba(255,255,255,0.78); font-size: 1.05rem; max-width: 720px; line-height: 1.8; margin-bottom: 34px; }

        .challenge-detail-card { background: rgba(255,255,255,0.05); border-radius: 30px; padding: 36px; border: 1px solid rgba(255,255,255,0.12); box-shadow: 0 22px 50px rgba(0,0,0,0.18); max-width: 900px; margin-top: 24px; }
        .challenge-detail-card h3 { font-size: 2.4rem; color: #fff; margin-bottom: 18px; }

        .question-box { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.14); border-radius: 28px; padding: 32px; margin-bottom: 24px; }
        .question-header { display: inline-flex; background: rgba(37,99,235,0.15); color: #bfdbfe; padding: 10px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 18px; }
        .question-text { color: #fff; font-size: 1.25rem; line-height: 1.7; }

        .question-list { display: flex; flex-direction: column; gap: 18px; margin-bottom: 24px; }
        .question-card { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.12); border-radius: 28px; padding: 28px; }
        .question-card.completed { opacity: 0.95; }
        .question-meta { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 16px; color: rgba(255,255,255,0.7); font-size: 0.95rem; }
        .question-status { padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,0.08); }
        .question-status.done { background: rgba(20,184,125,0.18); color: #d1fae5; }
        .question-status.open { background: rgba(245,158,11,0.16); color: #fde68a; }

        .answer-panel { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.12); border-radius: 24px; padding: 24px; margin-bottom: 24px; }
        .answer-panel label { display: block; color: rgba(255,255,255,0.75); font-weight: 700; margin-bottom: 12px; }
        .answer-panel textarea { width: 100%; min-height: 160px; resize: vertical; border: none; outline: none; background: transparent; color: #fff; font-size: 1rem; line-height: 1.8; padding: 0; }
        .answer-panel textarea::placeholder { color: rgba(255,255,255,0.45); }

        .option-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-top: 14px; }
        .option-card { position: relative; border-radius: 22px; padding: 22px 18px; background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.12); color: #fff; font-size: 1rem; font-weight: 700; cursor: pointer; transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease; display: flex; align-items: center; justify-content: center; min-height: 108px; text-align: center; overflow: hidden; }
        .option-card:hover { transform: translateY(-3px); background: rgba(255,255,255,0.12); border-color: rgba(255,255,255,0.22); }
        .option-card input { position: absolute; opacity: 0; pointer-events: none; }
        .option-card.selected { background: #2563eb; border-color: #3b82f6; box-shadow: 0 14px 30px rgba(37,99,235,0.24); }
        .option-content { display: flex; flex-direction: column; align-items: center; gap: 8px; width: 100%; }
        .option-letter { width: 34px; height: 34px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.16); font-size: 0.9rem; font-weight: 900; }
        .option-text { line-height: 1.55; }
        .option-indicator { position: absolute; top: 12px; right: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 8px 10px; border-radius: 999px; background: rgba(20,184,125,0.18); border: 1px solid rgba(20,184,125,0.34); color: #d1fae5; font-size: 0.78rem; font-weight: 800; letter-spacing: 0.04em; opacity: 0; transform: scale(0.92); transition: opacity 0.18s ease, transform 0.18s ease; }
        .option-card.selected .option-indicator { opacity: 1; transform: scale(1); }
        .option-card.selected .option-letter { background: #ffffff; border-color: #ffffff; color: #2563eb; }
        .option-card.selected .option-text { color: #fff; }

        .message-banner { display: flex; gap: 14px; align-items: center; padding: 20px 22px; border-radius: 24px; margin-bottom: 24px; }
        .message-banner.error { background: rgba(220,38,38,0.16); border: 1px solid rgba(220,38,38,0.28); color: #fee2e2; }
        .message-banner.success { background: rgba(20,184,125,0.16); border: 1px solid rgba(20,184,125,0.28); color: #d1fae5; }
        .message-banner strong { color: #ffffff; }

        .detail-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 18px; margin-bottom: 28px; }
        .detail-item { background: rgba(255,255,255,0.04); padding: 18px 20px; border-radius: 22px; border: 1px solid rgba(255,255,255,0.10); }
        .detail-key { font-size: 0.9rem; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; display: block; }
        .detail-value { color: #fff; font-size: 1rem; font-weight: 700; }

        .detail-actions { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; margin-top: 10px; }
        .detail-actions a, .detail-actions button { min-width: 220px; }
        .detail-actions .btn { width: 100%; max-width: 280px; }

        @media (max-width: 980px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { width: 100%; }
            .main-content { padding: 32px 24px; }
            .detail-actions .btn { max-width: 100%; }
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
                <h2 class="section-title">Detail Tantangan</h2>
                <p class="page-description">Kerjakan semua soal yang diberikan guru. Jawabanmu akan dicatat ke progres siswa.</p>
            </div>

            <div class="challenge-detail-card">
                <?php if ($completed): ?>
                    <div class="message-banner success">
                        <span>✅</span>
                        <div>
                            <strong>Selamat!</strong> Kamu telah menyelesaikan tantangan ini.
                        </div>
                    </div>
                <?php endif; ?>

                <h3><?= htmlspecialchars($challenge['title']) ?></h3>

                <?php if (!empty($challenge['description'])): ?>
                    <div class="question-box">
                        <div class="question-header">Deskripsi</div>
                        <div class="question-text"><?= nl2br(htmlspecialchars($challenge['description'])) ?></div>
                    </div>
                <?php endif; ?>

                <?php if ($error === 'wrong'): ?>
                    <div class="message-banner error">
                        <strong>Jawaban Salah.</strong> Coba lagi atau periksa kembali soal.
                    </div>
                <?php elseif ($error === 'empty'): ?>
                    <div class="message-banner error">
                        <strong>Silakan pilih atau isi jawaban terlebih dahulu.</strong>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/challenge/complete/<?= intval($challenge['id']) ?>">
                    <?php if (!empty($questions)): ?>
                        <div class="question-list">
                            <?php foreach ($questions as $index => $question): ?>
                                <?php
                                    $hasOptions = !empty($question['option_a']) || !empty($question['option_b']) || !empty($question['option_c']) || !empty($question['option_d']);
                                    $questionStatus = $question['user_status'] ?? 'open';
                                    $userAnswer = $question['user_answer'] ?? '';
                                ?>
                                <div class="question-card <?= $questionStatus === 'completed' ? 'completed' : '' ?>">
                                    <div class="question-meta">
                                        <span>Pertanyaan <?= $index + 1 ?></span>
                                        <span class="question-status <?= $questionStatus === 'completed' ? 'done' : 'open' ?>">
                                            <?= $questionStatus === 'completed' ? 'Selesai' : 'Belum selesai' ?>
                                        </span>
                                    </div>

                                    <div class="question-text"><?= nl2br(htmlspecialchars($question['question_text'])) ?></div>

                                    <?php if ($hasOptions): ?>
                                        <div class="option-list">
                                            <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field): ?>
                                                <?php if (!empty($question[$field])): ?>
                                                    <label class="option-card">
                                                        <input type="radio" name="answer[<?= intval($question['id']) ?>]" value="<?= $letter ?>" <?= ($userAnswer === $letter) ? 'checked' : '' ?> <?= ($questionStatus === 'completed') ? 'disabled' : '' ?>>
                                                        <span class="option-content">
                                                            <span class="option-letter"><?= $letter ?></span>
                                                            <span class="option-text"><?= htmlspecialchars($question[$field]) ?></span>
                                                        </span>
                                                        <span class="option-indicator">Dipilih ✓</span>
                                                    </label>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="answer-panel">
                                            <label for="answer-<?= intval($question['id']) ?>">Jawabanmu</label>
                                            <textarea id="answer-<?= intval($question['id']) ?>" name="answer[<?= intval($question['id']) ?>]" placeholder="Tulis jawaban di sini..." <?= ($questionStatus === 'completed') ? 'disabled' : '' ?>><?= htmlspecialchars($userAnswer) ?></textarea>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="question-box">
                            <div class="question-header">Soal</div>
                            <div class="question-text"><?= nl2br(htmlspecialchars($challenge['description'])) ?></div>
                        </div>
                        <div class="answer-panel">
                            <label for="answer">Jawabanmu</label>
                            <textarea id="answer" name="answer" placeholder="Tulis jawaban di sini..."><?= htmlspecialchars($challenge['user_answer'] ?? '') ?></textarea>
                        </div>
                    <?php endif; ?>

                    <div class="detail-actions">
                        <?php if ($role === 'teacher'): ?>
                            <a href="/challenge/edit/<?= intval($challenge['id']) ?>" class="btn btn-secondary">Edit Tantangan</a>
                            <a href="/challenge" class="btn btn-primary">Kembali ke daftar</a>
                        <?php else: ?>
                            <?php if (($challenge['status'] ?? null) === 'completed'): ?>
                                <button class="btn btn-done" disabled>Sudah Selesai ✓</button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-primary">Selesai</button>
                            <?php endif; ?>
                            <a href="/challenge" class="btn btn-secondary">Kembali</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="detail-list">
                    <div class="detail-item">
                        <span class="detail-key">Mata Pelajaran</span>
                        <span class="detail-value"><?= htmlspecialchars($challenge['category'] ?: 'Umum') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-key">Kesulitan</span>
                        <span class="detail-value"><?= htmlspecialchars($challenge['difficulty'] ?: 'Medium') ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-key">Minggu</span>
                        <span class="detail-value"><?= htmlspecialchars($challenge['week']) ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-key">Ditetapkan oleh</span>
                        <span class="detail-value"><?= htmlspecialchars($challenge['created_by_name'] ?: 'Admin') ?></span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.querySelectorAll('.option-card').forEach((card) => {
            const input = card.querySelector('input[type="radio"]');
            if (!input) return;

            const syncCardState = () => {
                card.classList.toggle('selected', input.checked);
            };

            input.addEventListener('change', () => {
                const groupName = input.name;
                document.querySelectorAll(`input[type="radio"][name="${CSS.escape(groupName)}"]`).forEach((otherInput) => {
                    const otherCard = otherInput.closest('.option-card');
                    if (otherCard) {
                        otherCard.classList.toggle('selected', otherInput.checked);
                    }
                });
            });

            syncCardState();
        });
    </script>
</body>
</html>
