<?php

namespace App\Controllers;

class AuthController {
    
    private $conn;
    
    public function __construct() {
        $this->conn = $GLOBALS['conn'] ?? null;
    }
    
    public function login() {
        // Show login form
        require __DIR__ . '/../views/auth/login.php';
    }
    
    public function register() {
        // Show register form
        require __DIR__ . '/../views/auth/register.php';
    }
    
    public function processLogin() {
        if (!$this->conn) {
            die('Database connection failed');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $password = $_POST['password'] ?? '';
            
            // Check teachers table
            $stmt = $this->conn->prepare("SELECT id, username, password FROM teachers WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = 'teacher';
                    header('Location: /profile');
                    exit;
                }
            }
            
            // Check students table
            $stmt = $this->conn->prepare("SELECT id, username, password FROM students WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($password === $user['password']) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = 'student';
                    header('Location: /profile');
                    exit;
                }
            }
            
            $error = "Username atau password salah!";
            require __DIR__ . '/../views/auth/login.php';
        }
    }
    
    public function processRegister() {
        if (!$this->conn) {
            die('Database connection failed');
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = isset($_POST['username']) ? trim($_POST['username']) : '';
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            $role = $_POST['role'] ?? '';
            
            if ($password !== $confirm_password) {
                $error = "Konfirmasi password tidak cocok!";
                require __DIR__ . '/../views/auth/register.php';
                return;
            }
            
            if (strlen($password) < 6) {
                $error = "Password minimal 6 karakter!";
                require __DIR__ . '/../views/auth/register.php';
                return;
            }
            
            if (!in_array($role, ['teacher', 'student'])) {
                $error = "Role tidak valid!";
                require __DIR__ . '/../views/auth/register.php';
                return;
            }
            
            $table = $role . 's'; // teachers or students
            
            // Check if username exists
            $stmt = $this->conn->prepare("SELECT id FROM $table WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Username sudah terdaftar!";
                require __DIR__ . '/../views/auth/register.php';
                return;
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert
            $stmt = $this->conn->prepare("INSERT INTO $table (username, password, email) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            if ($stmt->execute()) {
                header('Location: /login?registered=true');
                exit;
            } else {
                $error = "Terjadi kesalahan sistem.";
                require __DIR__ . '/../views/auth/register.php';
            }
        }
    }
    
    public function logout() {
        session_start();
        session_destroy();
        header('Location: /');
        exit;
    }
}
