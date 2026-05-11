<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
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
                        <label for="description">Deskripsi</label>
                        <textarea id="description" name="description" rows="5" required><?= htmlspecialchars($challenge['description'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="category">Kategori</label>
                        <input type="text" id="category" name="category" value="<?= htmlspecialchars($challenge['category'] ?? '') ?>" placeholder="Contoh: Matematika, Bahasa, dll.">
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
            </div>
        </main>
    </div>
</body>
</html>
