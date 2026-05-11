<?php
namespace App\Controllers;

class ProgressController {
    private $conn;

    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? null;
    }

    private function ensureConnection() {
        if (!$this->conn) {
            die('Database connection failed');
        }
    }

    public function Progress() {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $current_week = $GLOBALS['current_week'] ?? 1;

        // Hitung Total Challenge minggu ini
        $total_query = $this->conn->prepare("SELECT COUNT(*) as count FROM challenges WHERE week = ?");
        $total_query->bind_param('i', $current_week);
        $total_query->execute();
        $total_result = $total_query->get_result();
        $total_challenges = $total_result->fetch_assoc()['count'];

        // Hitung Challenge yang sudah 'completed' oleh user minggu ini
        $completed_query = $this->conn->prepare("
            SELECT COUNT(*) as count FROM user_challenges uc 
            JOIN challenges c ON uc.challenge_id = c.id 
            WHERE c.week = ? AND uc.user_id = ? AND uc.status = 'completed'
        ");
        $completed_query->bind_param('ii', $current_week, $user_id);
        $completed_query->execute();
        $completed_result = $completed_query->get_result();
        $completed_challenges = $completed_result->fetch_assoc()['count'];

        // Hitung persentase
        $progress_percentage = 0;
        if ($total_challenges > 0) {
            $progress_percentage = round(($completed_challenges / $total_challenges) * 100);
        }

        require_once __DIR__ . '/../views/home/progress.php';
    }
}