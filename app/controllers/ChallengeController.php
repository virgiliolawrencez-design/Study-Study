<?php
namespace App\Controllers;

class ChallengeController {
    private $conn;

    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? null;
    }

    private function ensureConnection() {
        if (!$this->conn) {
            die('Database connection failed');
        }
    }

    public function Challenge() {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $current_week = $GLOBALS['current_week'] ?? 1;
        $user_id = $_SESSION['user_id'];
        $role = $_SESSION['role'] ?? 'student';

        if ($role === 'teacher') {
            // Teachers see all challenges for management
            $query = "SELECT c.*, t.username AS created_by_name FROM challenges c LEFT JOIN teachers t ON c.created_by = t.id WHERE c.week = ? ORDER BY c.week, c.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('i', $current_week);
            $stmt->execute();
            $result = $stmt->get_result();
            $challenges = $result->fetch_all(MYSQLI_ASSOC);
        } else {
            // Students see challenges with their completion status
            $query = "SELECT c.*, t.username AS created_by_name, uc.status 
                     FROM challenges c 
                     LEFT JOIN teachers t ON c.created_by = t.id 
                     LEFT JOIN user_challenges uc ON c.id = uc.challenge_id AND uc.user_id = ? 
                     WHERE c.week = ? 
                     ORDER BY c.week, c.id";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param('ii', $user_id, $current_week);
            $stmt->execute();
            $result = $stmt->get_result();
            $challenges = $result->fetch_all(MYSQLI_ASSOC);
        }

        $totalChallenges = count($challenges);
        $completedChallenges = 0;
        foreach ($challenges as $challenge) {
            if (($challenge['status'] ?? null) === 'completed') {
                $completedChallenges++;
            }
        }
        $pendingChallenges = max(0, $totalChallenges - $completedChallenges);

        require_once __DIR__ . '/../views/challenge/index.php';
    }

    public function create() {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        $current_week = $GLOBALS['current_week'] ?? 1;
        require_once __DIR__ . '/../views/challenge/create.php';
    }

    public function store() {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $difficulty = trim($_POST['difficulty'] ?? 'Medium');
        $week = intval($_POST['week'] ?? ($GLOBALS['current_week'] ?? 1));
        $created_by = $_SESSION['user_id'];

        if ($title === '') {
            $error = 'Judul tantangan tidak boleh kosong.';
            require_once __DIR__ . '/../views/challenge/create.php';
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO challenges (title, description, category, difficulty, week, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssii', $title, $description, $category, $difficulty, $week, $created_by);
        if ($stmt->execute()) {
            header('Location: /challenge');
            exit;
        }

        $error = 'Gagal menyimpan tantangan.';
        require_once __DIR__ . '/../views/challenge/create.php';
    }

    public function edit($id) {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $stmt = $this->conn->prepare("SELECT * FROM challenges WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $challenge = $result->fetch_assoc();

        if (!$challenge) {
            http_response_code(404);
            echo 'Tantangan tidak ditemukan.';
            return;
        }

        $current_week = $GLOBALS['current_week'] ?? 1;
        require_once __DIR__ . '/../views/challenge/edit.php';
    }

    public function update($id) {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $difficulty = trim($_POST['difficulty'] ?? 'Medium');
        $week = intval($_POST['week'] ?? ($GLOBALS['current_week'] ?? 1));

        if ($title === '') {
            $error = 'Judul tantangan tidak boleh kosong.';
            $stmt = $this->conn->prepare("SELECT * FROM challenges WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $challenge = $stmt->get_result()->fetch_assoc();
            require_once __DIR__ . '/../views/challenge/edit.php';
            return;
        }

        $stmt = $this->conn->prepare("UPDATE challenges SET title = ?, description = ?, category = ?, difficulty = ?, week = ? WHERE id = ?");
        $stmt->bind_param('sssiii', $title, $description, $category, $difficulty, $week, $id);
        if ($stmt->execute()) {
            header('Location: /challenge');
            exit;
        }

        $error = 'Gagal memperbarui tantangan.';
        $stmt = $this->conn->prepare("SELECT * FROM challenges WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $challenge = $stmt->get_result()->fetch_assoc();
        require_once __DIR__ . '/../views/challenge/edit.php';
    }

    public function delete($id) {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $stmt = $this->conn->prepare("DELETE FROM challenges WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        header('Location: /challenge');
        exit;
    }

    public function complete($id) {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];

        // Insert or update completion status
        $stmt = $this->conn->prepare("INSERT INTO user_challenges (user_id, challenge_id, status) VALUES (?, ?, 'completed') ON DUPLICATE KEY UPDATE status = 'completed'");
        $stmt->bind_param('ii', $user_id, $id);
        $stmt->execute();

        header('Location: /challenge');
        exit;
    }
}
