<?php
namespace App\Controllers;

class HistoryController {
    public function History() {
        $conn = $GLOBALS['conn'] ?? null;
        require_once __DIR__ . '/../views/home/history.php';
    }
}
