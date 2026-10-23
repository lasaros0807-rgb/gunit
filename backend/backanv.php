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
            $message = 'Fyll i anvandarnamn, namn och losenord.';
        } else {
            $check = $forum->prepare('SELECT 1 FROM backenduser WHERE anvandarnamn = :username');
            $check->execute(['username' => $username]);

            if ($check->fetchColumn()) {
                $message = 'Anvandarnamnet anvands redan.';
            } else {
                $insert = $forum->prepare(
                    'INSERT INTO backenduser (anvandarnamn, namn, password) VALUES (:username, :name, :password)'
                );
                $insert->execute([
                    'username' => $username,
                    'name' => $name,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $message = 'Anvandaren har skapats.';
                $username = '';
                $name = '';
            }
        }
    }
} catch (PDOException $e) {
    if ($e->getCode() === '22001') {
        $databaseError = 'Losenordskolumnen ar for kort for losenordets sakra hash. Andra kolumnen i databasen och forsok igen.';
        $passwordColumnTooShort = true;
    } else {
        $databaseError = 'Databasfel vid skapande av anvandare: ' . $e->getMessage();
    }
}

if (isset($forum)) {
    try {
        $usersStatement = $forum->query(
            'SELECT anvandarnamn, namn FROM backenduser ORDER BY anvandarnamn'
        );
        $users = $usersStatement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $usersError = 'Kunde inte lasa anvandarna fran databasen: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera anvandare</title>
</head>
<body>
    <h1>Hantera anvandare</h1>
    <?php if ($message !== ''): ?>
        <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($databaseError !== ''): ?>
        <p>Databasfel: <?= htmlspecialchars($databaseError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($passwordColumnTooShort): ?>
            <p>Kor foljande SQL i databasen och forsok sedan skapa anvandaren igen:</p>
            <pre><code>ALTER TABLE backenduser MODIFY password VARCHAR(255);</code></pre>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($usersError !== ''): ?>
        <p><?= htmlspecialchars($usersError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Anvandarnamn:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="name">Namn:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="password">Losenord:</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>
        <br>
        <button type="submit">Skapa anvandare</button>
    </form>
    <h2>Skapade anvandare</h2>
    <?php if (count($users) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th scope="col">Anvandarnamn</th>
                    <th scope="col">Namn</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['anvandarnamn'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($user['namn'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Det finns inga anvandare annu.</p>
    <?php endif; ?>
    <p><a href="meny.php">Tillbaka till menyn</a></p>
</body>
</html>