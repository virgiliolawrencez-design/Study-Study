<?php
session_start();
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/Router.php';

$GLOBALS['conn'] = $conn;
$router = new \App\Core\Router();

// Essential routes for landing and auth
$router->add('GET', '/', 'LandingController', 'index');
$router->add('GET', '/login', 'AuthController', 'login');
$router->add('POST', '/login', 'AuthController', 'processLogin');
$router->add('GET', '/register', 'AuthController', 'register');
$router->add('POST', '/register', 'AuthController', 'processRegister');
$router->add('GET', '/logout', 'AuthController', 'logout');
$router->add('GET', '/challenge', 'ChallengeController', 'Challenge');
$router->add('GET', '/challenge/create', 'ChallengeController', 'create');
$router->add('POST', '/challenge/create', 'ChallengeController', 'store');
$router->add('GET', '/challenge/edit/{id}', 'ChallengeController', 'edit');
$router->add('POST', '/challenge/edit/{id}', 'ChallengeController', 'update');
$router->add('POST', '/challenge/delete/{id}', 'ChallengeController', 'delete');
$router->add('POST', '/challenge/complete/{id}', 'ChallengeController', 'complete');
$router->add('GET', '/progress', 'ProgressController', 'Progress');
$router->add('GET', '/history', 'HistoryController', 'History');
$router->add('GET', '/profile', 'ProfileController', 'Profile');

$router->run();
?>
