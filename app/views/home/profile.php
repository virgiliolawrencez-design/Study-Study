<?php
// session_start(); // Already started in index.php
$conn = $GLOBALS['conn'] ?? null;

if (!$conn) {
    die('Database connection failed');
}

// Jika belum login, tendang kembali ke halaman login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /login");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$table = $role . 's'; // teachers or students

// Ambil data spesifik user yang login
$query = "SELECT username, email FROM $table WHERE id = $user_id";
$result = $conn->query($query);
$user_data = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyTrack - Profile</title>
    <link rel="stylesheet" href="/css/home.css">
    
    <style>
        .main-content {
            padding: 40px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .profile-card {
            width: 100%;
            max-width: 900px;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 25px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .avatar-circle {
            width: 90px;
            height: 90px;
            background-color: #2f374e;
            border-radius: 50%;
        }

        .user-info h2 {
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 5px;
            color: #fff;
        }

        .user-info p {
            font-size: 1rem;
            color: rgba(255,255,255,0.78);
        }

        .profile-actions {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-outline {
            background: rgba(255,255,255,0.08);
            border: 1px solid #ff7f11;
            padding: 12px 22px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            color: #ffb36c;
            transition: 0.2s;
        }

        .btn-outline:hover {
            background: rgba(255,255,255,0.16);
        }

        .notif-section {
            border-bottom: 1px solid rgba(255,255,255,0.12);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .notif-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .notif-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .notif-group-title {
            font-weight: 800;
            margin-bottom: 15px;
            display: block;
            font-size: 1.05rem;
            color: #fff;
        }

        .notif-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 1rem;
            color: rgba(255,255,255,0.82);
        }

        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
        }

        .switch input { opacity: 0; width: 0; height: 0; }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: #0e1a2d;
            transition: .3s;
            border-radius: 34px;
            border: 1px solid rgba(255,255,255,0.12);
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px; width: 18px;
            left: 3px; bottom: 3px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:not(:checked) + .slider {
            background-color: rgba(255,255,255,0.1);
        }
        
        input:not(:checked) + .slider:before {
            transform: translateX(0);
        }

        input:checked + .slider:before {
            transform: translateX(22px);
        }

        .dropdown-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .custom-select {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            padding: 12px 16px;
            border-radius: 16px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            outline: none;
            color: #fff;
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
            <a href="/profile" class="menu-item profile active">Profile</a>
            <a href="/logout" class="menu-item logout">Logout</a>
        </nav>
        <div class="sidebar-mascot">
            <img src="/assets/Image1.png" alt="Mascot">
        </div>
    </aside>

    <main class="main-content">
        
        <div class="profile-card">
            <h2 class="section-title">Profile</h2>
            
            <div class="profile-header">
                <div class="avatar-circle"></div>
                <div class="user-info">
                    <h2><?php echo htmlspecialchars($user_data['username']); ?></h2>
                    <p><?php echo htmlspecialchars($user_data['email']); ?></p>
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn-outline">Edit Profile</button>
                <button class="btn-outline">Kelola Akun</button>
            </div>
        </div>

        <div class="profile-card">
            <h2 class="section-title">Notifikasi Email</h2>
            
            <div class="notif-section notif-grid">
                <div>
                    <span class="notif-group-title">Aktivitas</span>
                    <div class="notif-item">Komentar <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                    <div class="notif-item">Mention <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                    <div class="notif-item">Nilai & feedback <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                </div>
                <div>
                    <span class="notif-group-title">&nbsp;</span>
                    <div class="notif-item">Update kelas <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                    <div class="notif-item">Tugas baru <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                </div>
            </div>

            <div class="notif-section notif-grid">
                <div>
                    <span class="notif-group-title">Kelas & Tugas</span>
                    <div class="notif-item">Update kelas <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                    <div class="notif-item">Tugas baru <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                </div>
                <div>
                    <span class="notif-group-title">&nbsp;</span>
                    <div class="notif-item">Nilai & feedback <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                    <div class="notif-item">Deadline reminder <label class="switch"><input type="checkbox" checked><span class="slider"></span></label></div>
                </div>
            </div>
        </div>

        <div class="profile-card dropdown-wrapper">
            <h2 class="section-title" style="margin-bottom: 0;">Notifikasi per Kelas</h2>
            <select class="custom-select">
                <option>Atur per Kelas</option>
                <option>Matematika</option>
                <option>Bahasa Inggris</option>
            </select>
        </div>

    </main>
</div>

</body>
</html>