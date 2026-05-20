<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
$questions = $questions ?? [
    [
        'text' => '',
        'option_a' => '',
        'option_b' => '',
        'option_c' => '',
        'option_d' => '',
        'correct_option' => ''
    ]
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Tantangan - StudyTrack</title>
    <link rel="stylesheet" href="/css/home.css">
    <style>
        .form-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 40px;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            border: 2px solid #333;
            position: relative;
            overflow: hidden;
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, #215b9c, #EAE0CF);
        }

        .form-card h2 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #000;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 700;
            color: #000;
            font-size: 1.1rem;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 15px 20px;
            border-radius: 15px;
            border: 2px solid #333;
            font-size: 1rem;
            background: #fff;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #215b9c;
            box-shadow: 0 0 10px rgba(33, 91, 156, 0.2);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-primary {
            background: #215b9c;
            color: #fff;
            border: 2px solid #333;
            padding: 15px 30px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            text-align: center;
            text-decoration: none;
        }

        .btn-primary:hover {
            background: #1a4a7a;
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(33, 91, 156, 0.3);
        }

        .top-link {
            display: inline-block;
            margin-bottom: 30px;
            color: #215b9c;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .top-link:hover {
            color: #1a4a7a;
        }

        .alert {
            background: #ff6b6b;
            color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #333;
        }

        .question-card {
            background: rgba(33, 91, 156, 0.08);
            border: 2px solid rgba(33, 91, 156, 0.3);
            border-radius: 18px;
            padding: 24px;
            margin-bottom: 22px;
        }

        .question-card h3 {
            margin: 0 0 16px;
            color: #072a4f;
            font-size: 1.25rem;
        }

        .question-controls {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 20px;
        }

        .question-card .form-group {
            margin-bottom: 16px;
        }

        .button-add {
            background: #10b981;
            color: #fff;
            border: 2px solid #0f766e;
            padding: 12px 20px;
            border-radius: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
        }

        .button-add:hover {
            transform: translateY(-2px);
            background: #0f766e;
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
                <a href="/history" class="menu-item history">History</a>
                <a href="/profile" class="menu-item profile">Profile</a>
                <a href="/logout" class="menu-item logout">Logout</a>
            </nav>
        </aside>
        <main class="main-content">
            <div class="form-card">
                <a href="/challenge" class="top-link">← Kembali ke Daftar Tantangan</a>
                <h2>Edit Tantangan</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST" action="/challenge/edit/<?= intval($challenge['id']) ?>">
                    <div class="form-group">
                        <label for="title">Judul Tantangan</label>
                        <input type="text" id="title" name="title" required value="<?= htmlspecialchars($challenge['title'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="description">Soal / Deskripsi Tantangan</label>
                        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($challenge['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category">Mata Pelajaran</label>
                        <input type="text" id="category" name="category" value="<?= htmlspecialchars($challenge['category'] ?? '') ?>" placeholder="Contoh: PPKN, Matematika, Bahasa Indonesia">
                    </div>
                    <div class="question-controls">
                        <button type="button" class="button-add" onclick="addQuestion()">+ Tambah Soal</button>
                    </div>
                    <div id="question-list">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-card" data-index="<?= intval($index) ?>">
                                <h3>Soal <?= intval($index) + 1 ?></h3>
                                <div class="form-group">
                                    <label for="questions_<?= intval($index) ?>_text">Teks Soal</label>
                                    <textarea id="questions_<?= intval($index) ?>_text" name="questions[<?= intval($index) ?>][text]" rows="3" required><?= htmlspecialchars($question['text'] ?? '') ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="questions_<?= intval($index) ?>_option_a">Pilihan A</label>
                                    <input type="text" id="questions_<?= intval($index) ?>_option_a" name="questions[<?= intval($index) ?>][option_a]" value="<?= htmlspecialchars($question['option_a'] ?? '') ?>" placeholder="Contoh: 67 atau Jawaban A">
                                </div>
                                <div class="form-group">
                                    <label for="questions_<?= intval($index) ?>_option_b">Pilihan B</label>
                                    <input type="text" id="questions_<?= intval($index) ?>_option_b" name="questions[<?= intval($index) ?>][option_b]" value="<?= htmlspecialchars($question['option_b'] ?? '') ?>" placeholder="Contoh: 63 atau Jawaban B">
                                </div>
                                <div class="form-group">
                                    <label for="questions_<?= intval($index) ?>_option_c">Pilihan C</label>
                                    <input type="text" id="questions_<?= intval($index) ?>_option_c" name="questions[<?= intval($index) ?>][option_c]" value="<?= htmlspecialchars($question['option_c'] ?? '') ?>" placeholder="Contoh: 57 atau Jawaban C">
                                </div>
                                <div class="form-group">
                                    <label for="questions_<?= intval($index) ?>_option_d">Pilihan D</label>
                                    <input type="text" id="questions_<?= intval($index) ?>_option_d" name="questions[<?= intval($index) ?>][option_d]" value="<?= htmlspecialchars($question['option_d'] ?? '') ?>" placeholder="Contoh: 77 atau Jawaban D">
                                </div>
                                <div class="form-group">
                                    <label for="questions_<?= intval($index) ?>_correct_option">Kunci Jawaban</label>
                                    <select id="questions_<?= intval($index) ?>_correct_option" name="questions[<?= intval($index) ?>][correct_option]">
                                        <option value="" <?= empty($question['correct_option']) ? 'selected' : '' ?>>Tidak ada kunci jawaban</option>
                                        <option value="A" <?= ($question['correct_option'] ?? '') === 'A' ? 'selected' : '' ?>>A</option>
                                        <option value="B" <?= ($question['correct_option'] ?? '') === 'B' ? 'selected' : '' ?>>B</option>
                                        <option value="C" <?= ($question['correct_option'] ?? '') === 'C' ? 'selected' : '' ?>>C</option>
                                        <option value="D" <?= ($question['correct_option'] ?? '') === 'D' ? 'selected' : '' ?>>D</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="button-add" style="background:#ef4444;border-color:#b91c1c;" onclick="removeQuestion(this)">Hapus Soal</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-group">
                        <label for="difficulty">Tingkat Kesulitan</label>
                        <select id="difficulty" name="difficulty" required>
                            <option value="" disabled <?= empty($challenge['difficulty']) ? 'selected' : '' ?>>Pilih tingkat kesulitan</option>
                            <option value="Low" <?= ($challenge['difficulty'] ?? '') === 'Low' ? 'selected' : '' ?>>Low - Mudah</option>
                            <option value="Medium" <?= ($challenge['difficulty'] ?? '') === 'Medium' ? 'selected' : '' ?>>Medium - Sedang</option>
                            <option value="High" <?= ($challenge['difficulty'] ?? '') === 'High' ? 'selected' : '' ?>>High - Sulit</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="week">Minggu</label>
                        <input type="number" id="week" name="week" min="1" value="<?= htmlspecialchars($challenge['week'] ?? $current_week) ?>" required>
                    </div>
                    <button type="submit" class="btn-primary">Perbarui Tantangan</button>
                </form>
                <template id="question-template">
                    <div class="question-card" data-index="__INDEX__">
                        <h3>Soal __NUMBER__</h3>
                        <div class="form-group">
                            <label for="questions___INDEX___text">Teks Soal</label>
                            <textarea id="questions___INDEX___text" name="questions[__INDEX__][text]" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="questions___INDEX___option_a">Pilihan A</label>
                            <input type="text" id="questions___INDEX___option_a" name="questions[__INDEX__][option_a]" placeholder="Contoh: 67 atau Jawaban A">
                        </div>
                        <div class="form-group">
                            <label for="questions___INDEX___option_b">Pilihan B</label>
                            <input type="text" id="questions___INDEX___option_b" name="questions[__INDEX__][option_b]" placeholder="Contoh: 63 atau Jawaban B">
                        </div>
                        <div class="form-group">
                            <label for="questions___INDEX___option_c">Pilihan C</label>
                            <input type="text" id="questions___INDEX___option_c" name="questions[__INDEX__][option_c]" placeholder="Contoh: 57 atau Jawaban C">
                        </div>
                        <div class="form-group">
                            <label for="questions___INDEX___option_d">Pilihan D</label>
                            <input type="text" id="questions___INDEX___option_d" name="questions[__INDEX__][option_d]" placeholder="Contoh: 77 atau Jawaban D">
                        </div>
                        <div class="form-group">
                            <label for="questions___INDEX___correct_option">Kunci Jawaban</label>
                            <select id="questions___INDEX___correct_option" name="questions[__INDEX__][correct_option]">
                                <option value="">Tidak ada kunci jawaban</option>
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="C">C</option>
                                <option value="D">D</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="button" class="button-add" style="background:#ef4444;border-color:#b91c1c;" onclick="removeQuestion(this)">Hapus Soal</button>
                        </div>
                    </div>
                </template>
            </div>
        </main>
    </div>
    <script>
        function addQuestion() {
            const list = document.getElementById('question-list');
            const template = document.getElementById('question-template').innerHTML;
            const index = list.children.length;
            const html = template.replace(/__INDEX__/g, index).replace(/__NUMBER__/g, index + 1);
            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            list.appendChild(wrapper.firstElementChild);
            updateQuestionNumbers();
        }

        function removeQuestion(button) {
            const card = button.closest('.question-card');
            if (!card) return;
            card.remove();
            updateQuestionNumbers();
        }

        function updateQuestionNumbers() {
            const list = document.getElementById('question-list');
            Array.from(list.children).forEach((card, idx) => {
                card.dataset.index = idx;
                const title = card.querySelector('h3');
                if (title) {
                    title.textContent = 'Soal ' + (idx + 1);
                }
                card.querySelectorAll('[id]').forEach((el) => {
                    const parts = el.id.split('_');
                    const suffix = parts.slice(2).join('_');
                    el.id = 'questions_' + idx + '_' + suffix;
                });
                card.querySelectorAll('[name]').forEach((el) => {
                    const name = el.name.replace(/questions\[\d+\]/, 'questions[' + idx + ']');
                    el.name = name;
                });
            });
        }
    </script>
</body>
</html>
