<?php
require_once __DIR__ . '/../../config/db.php';

$conn = $GLOBALS['conn'] ?? $conn ?? null;

if (!$conn) {
    die('Database connection failed');
}

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$user_id = (int) $_SESSION['user_id'];
$history_data = [];

/**
 * Ambil semua challenge yang sudah selesai milik user.
 * Ini membuat challenge yang baru selesai langsung muncul di History.
 */
$history_query = $conn->prepare("
    SELECT
        c.id,
        c.title,
        c.description,
        c.category,
        c.difficulty,
        c.week,
        uc.status,
        uc.user_answer
    FROM user_challenges uc
    JOIN challenges c ON uc.challenge_id = c.id
    WHERE uc.user_id = ? AND uc.status = 'completed'
    ORDER BY c.week DESC, c.id DESC
");
$history_query->bind_param('i', $user_id);
$history_query->execute();
$history_result = $history_query->get_result();
$completed_challenges = $history_result->fetch_all(MYSQLI_ASSOC);

foreach ($completed_challenges as $challenge) {
    $week = (int) ($challenge['week'] ?? 1);

    if (!isset($history_data[$week])) {
        $history_data[$week] = [
            'total' => 0,
            'completed' => 0,
            'percentage' => 0,
            'challenges' => [],
        ];
    }

    $history_data[$week]['challenges'][] = $challenge;
}

foreach ($history_data as $week => &$data) {
    $total_query = $conn->prepare("SELECT COUNT(*) AS count FROM challenges WHERE week = ?");
    $total_query->bind_param('i', $week);
    $total_query->execute();
    $total_result = $total_query->get_result();
    $total = (int) ($total_result->fetch_assoc()['count'] ?? 0);

    $completed = count($data['challenges']);
    $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;

    $data['total'] = $total;
    $data['completed'] = $completed;
    $data['percentage'] = $percentage;

    foreach ($data['challenges'] as &$challenge) {
        $question_query = $conn->prepare("
            SELECT
                q.id,
                q.question_text,
                q.option_a,
                q.option_b,
                q.option_c,
                q.option_d,
                q.correct_option,
                u.answer AS user_answer,
                u.status AS user_status
            FROM challenge_questions q
            LEFT JOIN user_question_answers u
                ON q.id = u.challenge_question_id AND u.user_id = ?
            WHERE q.challenge_id = ?
            ORDER BY q.question_order, q.id
        ");
        $challengeId = (int) $challenge['id'];
        $question_query->bind_param('ii', $user_id, $challengeId);
        $question_query->execute();
        $question_result = $question_query->get_result();
        $challenge['questions'] = $question_result->fetch_all(MYSQLI_ASSOC);
    }
    unset($challenge);
}
unset($data);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>StudyTrack - History</title>
    <link rel="stylesheet" href="/css/home.css">
    <style>
        .history-list { display: flex; flex-direction: column; gap: 24px; }
        .history-card { display: flex; flex-direction: column; gap: 18px; }
        .history-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }
        .history-meta {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .history-meta label {
            color: #fff;
            font-weight: 800;
            font-size: 1.05rem;
        }
        .history-meta small {
            color: rgba(255,255,255,0.68);
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
        .history-review {
            border-top: 1px solid rgba(255,255,255,0.10);
            padding-top: 18px;
        }
        .history-review-title {
            color: #fff;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }
        .review-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        .review-item {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.10);
            border-radius: 18px;
            padding: 16px 18px;
        }
        .review-item summary {
            cursor: pointer;
            list-style: none;
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .review-item summary::-webkit-details-marker { display: none; }
        .review-item-title {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .review-item-title span:last-child {
            color: rgba(255,255,255,0.68);
            font-size: 0.82rem;
            font-weight: 600;
        }
        .challenge-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(37,99,235,0.18);
            border: 1px solid rgba(37,99,235,0.30);
            color: #bfdbfe;
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 800;
            transition: transform 0.2s ease, background 0.2s ease;
            white-space: nowrap;
        }
        .challenge-link:hover {
            transform: translateY(-1px);
            background: rgba(37,99,235,0.24);
        }
        .review-question-list {
            margin-top: 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .review-question {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 14px 16px;
        }
        .review-question h5 {
            color: #fff;
            font-size: 0.98rem;
            margin-bottom: 8px;
        }
        .review-question p {
            color: rgba(255,255,255,0.78);
            line-height: 1.7;
            margin: 0 0 8px;
        }
        .review-answer {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 8px;
        }
        .review-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 10px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
        }
        .badge-answer {
            background: rgba(37,99,235,0.16);
            border: 1px solid rgba(37,99,235,0.30);
            color: #bfdbfe;
        }
        .badge-correct {
            background: rgba(20,184,125,0.16);
            border: 1px solid rgba(20,184,125,0.30);
            color: #d1fae5;
        }
        .badge-wrong {
            background: rgba(220,38,38,0.16);
            border: 1px solid rgba(220,38,38,0.30);
            color: #fee2e2;
        }
        .empty-review {
            color: rgba(255,255,255,0.72);
            font-style: italic;
        }
        .arrow-icon {
            font-size: 2.2rem;
            cursor: pointer;
            transition: transform 0.3s ease;
            color: #ffb36c;
            align-self: flex-end;
            user-select: none;
        }
        .arrow-icon:hover { transform: scale(1.2); }
        .empty-state {
            padding: 24px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px dashed rgba(255,255,255,0.14);
            color: #fff;
        }
        .history-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .history-note {
            color: rgba(255,255,255,0.68);
            font-size: 0.9rem;
        }
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
            <?php if (empty($history_data)): ?>
                <div class="card empty-state">
                    <p style="font-size: 1.2rem; color: #fff;">Belum ada history challenge yang selesai.</p>
                </div>
            <?php else: ?>
                <?php foreach ($history_data as $minggu => $data): ?>
                    <div class="card history-card">
                        <div class="history-head">
                            <div class="history-meta">
                                <label>Progress Minggu <?= htmlspecialchars($minggu) ?></label>
                                <small><?= htmlspecialchars($data['completed']) ?> dari <?= htmlspecialchars($data['total']) ?> tantangan selesai</small>
                            </div>
                            <div class="arrow-icon">❯</div>
                        </div>

                        <div class="progress-mini-outline">
                            <div class="progress-mini-fill" style="width: <?= htmlspecialchars($data['percentage']) ?>%;"><?= htmlspecialchars($data['percentage']) ?>%</div>
                        </div>

                        <div class="history-review">
                            <div class="history-review-title">
                                <span>Review jawaban</span>
                                <span class="history-note">Klik tantangan untuk melihat jawabanmu</span>
                            </div>

                            <?php if (empty($data['challenges'])): ?>
                                <div class="empty-review">Belum ada tantangan yang selesai pada minggu ini.</div>
                            <?php else: ?>
                                <div class="review-list">
                                    <?php foreach ($data['challenges'] as $challenge): ?>
                                        <details class="review-item">
                                            <summary>
                                                <div class="review-item-title">
                                                    <span><?= htmlspecialchars($challenge['title']) ?></span>
                                                    <span>Minggu <?= htmlspecialchars($challenge['week']) ?> • <?= htmlspecialchars($challenge['category'] ?: 'Umum') ?></span>
                                                </div>
                                                <a class="challenge-link" href="/challenge/<?= intval($challenge['id']) ?>">Lihat jawaban</a>
                                            </summary>

                                            <div class="review-question-list">
                                                <?php if (empty($challenge['questions'])): ?>
                                                    <div class="empty-review">Belum ada soal tersimpan untuk tantangan ini.</div>
                                                <?php else: ?>
                                                    <?php foreach ($challenge['questions'] as $index => $question): ?>
                                                        <?php
                                                            $userAnswer = trim((string)($question['user_answer'] ?? ''));
                                                            $correctOption = strtoupper((string)($question['correct_option'] ?? ''));
                                                            $isCorrect = $correctOption !== '' && strtoupper($userAnswer) === $correctOption;
                                                            $answerText = $userAnswer;
                                                            $answerField = 'option_' . strtolower($userAnswer);
                                                            if (in_array($userAnswer, ['A', 'B', 'C', 'D'], true) && !empty($question[$answerField])) {
                                                                $answerText = $userAnswer . ' - ' . $question[$answerField];
                                                            }
                                                            $correctText = '';
                                                            if ($correctOption !== '') {
                                                                $correctField = 'option_' . strtolower($correctOption);
                                                                if (!empty($question[$correctField])) {
                                                                    $correctText = $correctOption . ' - ' . $question[$correctField];
                                                                } else {
                                                                    $correctText = $correctOption;
                                                                }
                                                            }
                                                        ?>
                                                        <div class="review-question">
                                                            <h5>Soal <?= $index + 1 ?></h5>
                                                            <p><?= nl2br(htmlspecialchars($question['question_text'])) ?></p>
                                                            <div class="review-answer">
                                                                <span class="review-badge badge-answer">Jawabanmu: <?= htmlspecialchars($answerText !== '' ? $answerText : '-') ?></span>
                                                                <?php if ($correctText !== ''): ?>
                                                                    <span class="review-badge badge-correct">Jawaban benar: <?= htmlspecialchars($correctText) ?></span>
                                                                <?php endif; ?>
                                                                <?php if ($correctOption !== '' && $userAnswer !== '' && !$isCorrect): ?>
                                                                    <span class="review-badge badge-wrong">Perlu ditinjau lagi</span>
                                                                <?php elseif ($isCorrect): ?>
                                                                    <span class="review-badge badge-correct">Benar</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </div>
                                        </details>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
