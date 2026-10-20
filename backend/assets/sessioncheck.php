<?php
// Initierar sessionshantering
session_start();

// Kontrollerar om sessionsvariabel existerar
if (!isset($_SESSION['username'])) {
    // Skickar en användare till startsidan
    header('Location: login.php');
    exit();
}
?>
