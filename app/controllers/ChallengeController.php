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

    private function saveQuestions(int $challengeId, array $questions) {
        $delete = $this->conn->prepare("DELETE FROM challenge_questions WHERE challenge_id = ?");
        $delete->bind_param('i', $challengeId);
        $delete->execute();

        $insert = $this->conn->prepare("INSERT INTO challenge_questions (challenge_id, question_text, option_a, option_b, option_c, option_d, correct_option, question_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($questions as $index => $question) {
            $questionText = trim($question['text'] ?? '');
            if ($questionText === '') {
                continue;
            }
            $optionA = trim($question['option_a'] ?? '');
            $optionB = trim($question['option_b'] ?? '');
            $optionC = trim($question['option_c'] ?? '');
            $optionD = trim($question['option_d'] ?? '');
            $correctOption = strtoupper(trim($question['correct_option'] ?? ''));
            if (!in_array($correctOption, ['A', 'B', 'C', 'D'], true)) {
                $correctOption = '';
            }
            $order = $index + 1;
            $insert->bind_param('issssssi', $challengeId, $questionText, $optionA, $optionB, $optionC, $optionD, $correctOption, $order);
            $insert->execute();
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
            $query = "SELECT c.*, t.username AS created_by_name FROM challenges c LEFT JOIN teachers t ON c.created_by = t.id ORDER BY c.week DESC, c.id DESC";
            $stmt = $this->conn->prepare($query);
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
        $questions = $_POST['questions'] ?? [];
        $created_by = $_SESSION['user_id'];

        if ($title === '') {
            $error = 'Judul tantangan tidak boleh kosong.';
            require_once __DIR__ . '/../views/challenge/create.php';
            return;
        }

        $stmt = $this->conn->prepare("INSERT INTO challenges (title, description, category, difficulty, week, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssii', $title, $description, $category, $difficulty, $week, $created_by);
        if ($stmt->execute()) {
            $this->saveQuestions($stmt->insert_id, $questions);
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

        $stmt = $this->conn->prepare("SELECT * FROM challenge_questions WHERE challenge_id = ? ORDER BY question_order, id");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

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
        $questions = $_POST['questions'] ?? [];

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
        $stmt->bind_param('ssssii', $title, $description, $category, $difficulty, $week, $id);
        if ($stmt->execute()) {
            $this->saveQuestions($id, $questions);
            header('Location: /challenge');
            exit;
        }

        $error = 'Gagal memperbarui tantangan.';
        $stmt = $this->conn->prepare("SELECT * FROM challenges WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $challenge = $stmt->get_result()->fetch_assoc();
        $stmt = $this->conn->prepare("SELECT * FROM challenge_questions WHERE challenge_id = ? ORDER BY question_order, id");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        require_once __DIR__ . '/../views/challenge/edit.php';
    }

    public function show($id) {
        $this->ensureConnection();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $stmt = $this->conn->prepare("SELECT c.*, t.username AS created_by_name, uc.status
            FROM challenges c
            LEFT JOIN teachers t ON c.created_by = t.id
            LEFT JOIN user_challenges uc ON c.id = uc.challenge_id AND uc.user_id = ?
            WHERE c.id = ?");
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param('ii', $user_id, $id);
        $stmt->execute();
        $challenge = $stmt->get_result()->fetch_assoc();

        if (!$challenge) {
            http_response_code(404);
            echo 'Tantangan tidak ditemukan.';
            return;
        }

        $stmt = $this->conn->prepare("SELECT q.*, u.answer AS user_answer, u.status AS user_status
            FROM challenge_questions q
            LEFT JOIN user_question_answers u ON q.id = u.challenge_question_id AND u.user_id = ?
            WHERE q.challenge_id = ?
            ORDER BY q.question_order, q.id");
        $stmt->bind_param('ii', $user_id, $id);
        $stmt->execute();
        $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $role = $_SESSION['role'] ?? 'student';
        require_once __DIR__ . '/../views/challenge/show.php';
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
        $answers = $_POST['answer'] ?? [];

        $stmt = $this->conn->prepare("SELECT id, correct_option FROM challenge_questions WHERE challenge_id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = $result->fetch_all(MYSQLI_ASSOC);

        if (!empty($questions)) {
            $missingAnswer = false;
            foreach ($questions as $question) {
                $questionId = $question['id'];
                $provided = trim((string)($answers[$questionId] ?? ''));
                if ($provided === '') {
                    $missingAnswer = true;
                    break;
                }
            }
            if ($missingAnswer) {
                header('Location: /challenge/' . intval($id) . '?error=empty');
                exit;
            }

            $allCorrect = true;
            $saveStmt = $this->conn->prepare("INSERT INTO user_question_answers (user_id, challenge_question_id, answer, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE answer = ?, status = ?");
            foreach ($questions as $question) {
                $questionId = $question['id'];
                $provided = trim((string)($answers[$questionId] ?? ''));
                $correctOption = strtoupper($question['correct_option'] ?? '');
                $status = 'open';
                if ($correctOption !== '') {
                    if (strtoupper($provided) === $correctOption) {
                        $status = 'completed';
                    } else {
                        $status = 'open';
                    }
                } else {
                    $status = $provided === '' ? 'open' : 'completed';
                }
                if ($status !== 'completed') {
                    $allCorrect = false;
                }
                $saveStmt->bind_param('iissss', $user_id, $questionId, $provided, $status, $provided, $status);
                $saveStmt->execute();
            }

            $overallStatus = $allCorrect ? 'completed' : 'open';
            $parentStmt = $this->conn->prepare("INSERT INTO user_challenges (user_id, challenge_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
            $parentStmt->bind_param('iiss', $user_id, $id, $overallStatus, $overallStatus);
            $parentStmt->execute();

            if ($allCorrect) {
                header('Location: /challenge/' . intval($id) . '?completed=1');
            } else {
                header('Location: /challenge/' . intval($id) . '?error=wrong');
            }
            exit;
        }

        $answer = trim($_POST['answer'] ?? '');
        $stmt = $this->conn->prepare("SELECT correct_option FROM challenges WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $challenge = $result->fetch_assoc();
        $correct_option = $challenge['correct_option'] ?? '';

        if (!empty($correct_option)) {
            if ($answer === '') {
                header('Location: /challenge/' . intval($id) . '?error=empty');
                exit;
            }
            $status = strtoupper($answer) === strtoupper($correct_option) ? 'completed' : 'open';
            $stmt = $this->conn->prepare("INSERT INTO user_challenges (user_id, challenge_id, status, user_answer) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = ?, user_answer = ?");
            $stmt->bind_param('iissss', $user_id, $id, $status, $answer, $status, $answer);
            $stmt->execute();
            if ($status === 'completed') {
                header('Location: /challenge/' . intval($id) . '?completed=1');
            } else {
                header('Location: /challenge/' . intval($id) . '?error=wrong');
            }
            exit;
        }

        $stmt = $this->conn->prepare("INSERT INTO user_challenges (user_id, challenge_id, status, user_answer) VALUES (?, ?, 'completed', ?) ON DUPLICATE KEY UPDATE status = 'completed', user_answer = ?");
        $stmt->bind_param('iiss', $user_id, $id, $answer, $answer);
        $stmt->execute();

        header('Location: /challenge/' . intval($id) . '?completed=1');
        exit;
    }
}
