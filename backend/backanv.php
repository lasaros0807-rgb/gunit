<?php
$message = '';
$databaseError = '';
$passwordColumnTooShort = false;
$usersError = '';
$username = '';
$name = '';
$users = [];

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $name === '' || $password === '') {
            $message = 'Fyll i användarnamn, namn och lösenord.';
        } else {
            $check = $forum->prepare('SELECT 1 FROM backenduser WHERE användarnamn = :username');
            $check->execute(['username' => $username]);

            if ($check->fetchColumn()) {
                $message = 'Användarnamnet används redan.';
            } else {
                $insert = $forum->prepare(
                    'INSERT INTO backenduser (användarnamn, namn, password) VALUES (:username, :name, :password)'
                );
                $insert->execute([
                    'username' => $username,
                    'name' => $name,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $message = 'Användaren har skapats.';
                $username = '';
                $name = '';
            }
        }
    }
} catch (PDOException $e) {
    if ($e->getCode() === '22001') {
        $databaseError = 'Lösenordskolumnen är för kort för lösenordets säkra hash. Ändra kolumnen i databasen och försök igen.';
        $passwordColumnTooShort = true;
    } else {
        $databaseError = 'Databasfel vid skapande av användare: ' . $e->getMessage();
    }
}

if (isset($forum)) {
    try {
        $usersStatement = $forum->query(
            'SELECT användarnamn, namn FROM backenduser ORDER BY användarnamn'
        );
        $users = $usersStatement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $usersError = 'Kunde inte läsa användarna från databasen: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera användare</title>
</head>
<body>
    <h1>Hantera användare</h1>
    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($databaseError !== ''): ?>
        <p>Databasfel: <?= htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($passwordColumnTooShort): ?>
            <p>Kör följande SQL i databasen och försök sedan skapa användaren igen:</p>
            <pre><code>ALTER TABLE backenduser MODIFY password VARCHAR(255);</code></pre>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($usersError !== ''): ?>
        <p><?= htmlspecialchars($usersError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Användarnamn:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="name">Namn:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="password">Lösenord:</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>
        <br>
        <button type="submit">Skapa användare</button>
    </form>
    <h2>Skapade användare</h2>
    <?php if (count($users) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th scope="col">Användarnamn</th>
                    <th scope="col">Namn</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['användarnamn'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user['namn'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Det finns inga användare ännu.</p>
    <?php endif; ?>
    <p><a href="meny.php">Tillbaka till menyn</a></p>
</body>
</html>