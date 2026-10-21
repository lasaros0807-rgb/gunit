<?php
//require_once 'sessioncheck.php';
require_once 'db.php';

function login($username, $password) {
    global $forum;

    $sql = "SELECT användarnamn, password FROM backenduser WHERE användarnamn = :username";
    $stmt = $forum->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        return false;
    }

    $storedPassword = (string) $user['password'];
    if (password_verify($password, $storedPassword)) {
        return true;
    }

    if (!hash_equals($storedPassword, $password)) {
        return false;
    }

    $update = $forum->prepare(
        'UPDATE backenduser SET password = :password WHERE användarnamn = :username'
    );
    $update->execute([
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'username' => $username,
    ]);

    return true;
}





?>