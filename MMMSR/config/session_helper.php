<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $dirName = dirname($scriptName);
    $basePath = preg_replace('#/(admin|api|pages|install|config)$#', '', $dirName);
    return rtrim($basePath, '/') . '/';
}

function checkSession() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: " . getBasePath() . "login.php");
        exit();
    }
}

function getCurrentUserName() {
    return isset($_SESSION['username']) ? $_SESSION['username'] : 'Utilisateur';
}

function getCurrentUserRole() {
    if (isset($_SESSION['roles'])) {
        $roles = json_decode($_SESSION['roles'], true);
        if (is_array($roles) && count($roles) > 0) {
            return ucfirst(strtolower($roles[0]));
        }
    }
    return 'Invité';
}
