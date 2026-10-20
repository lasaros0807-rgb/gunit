<?php
//require_once 'sessioncheck.php';
require_once 'db.php';

function login($username, $password) {
    global $forum;

    $sql = "SELECT * FROM backenduser WHERE användarnamn = :username AND password = :password";
    $stmt = $forum->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->bindValue(':password', $password);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        return true;
    } else {
        return false;
    }
}





?>