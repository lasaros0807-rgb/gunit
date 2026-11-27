<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logga in</title>
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    <main>
        <h1>Logga in</h1>
        <form action="../../backend/index.php" method="post">
            <label for="username">Användarnamn:</label>
            <input id="username" name="username" type="text" autocomplete="username" required>
            <br>
            <label for="password">Lösenord:</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <br>
            <button type="submit" name="submit" value="login">Logga in</button>
        </form>
    </main>
</body>
</html>