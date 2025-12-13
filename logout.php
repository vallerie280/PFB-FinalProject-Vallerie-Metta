<?php 
session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000 , $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

setcookie('remember_user_id', '', time() - 3600, "/");
setcookie('remember_username', '', time() - 3600, "/");

session_destroy();

header('Location: login.php?status=logout');
exit;
?>