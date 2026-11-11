<?php
$meddelande = '';
$databasfel = '';
$losenordskolumnForKort = false;
$anvandarfel = '';
$anvandarnamn = '';
$namn = '';
$anvandare = [];

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $anvandarnamn = trim($_POST['username'] ?? '');
        $namn = trim($_POST['name'] ?? '');
        $losenord = $_POST['password'] ?? '';

        if ($anvandarnamn === '' || $namn === '' || $losenord === '') {
            $meddelande = 'Fyll i anvandarnamn, namn och losenord.';
        } else {
            $kontroll = $forum->prepare('SELECT 1 FROM backenduser WHERE anvandarnamn = :anvandarnamn');
            $kontroll->execute(['anvandarnamn' => $anvandarnamn]);

            if ($kontroll->fetchColumn()) {
                $meddelande = 'Anvandarnamnet anvands redan.';
            } else {
                $sparning = $forum->prepare(
                    'INSERT INTO backenduser (anvandarnamn, namn, password) VALUES (:anvandarnamn, :namn, :losenord)'
                );
                $sparning->execute([
                    'anvandarnamn' => $anvandarnamn,
                    'namn' => $namn,
                    'losenord' => password_hash($losenord, PASSWORD_DEFAULT),
                ]);
                $meddelande = 'Backendanvandaren har skapats.';
                $anvandarnamn = '';
                $namn = '';
            }
        }
    }
} catch (PDOException $e) {
    if ($e->getCode() === '22001') {
        $databasfel = 'Losenordskolumnen ar for kort for losenordets sakra hash. Andra kolumnen i databasen och forsok igen.';
        $losenordskolumnForKort = true;
    } else {
        $databasfel = 'Databasfel vid skapande av backendanvandare: ' . $e->getMessage();
    }
}

if (isset($forum)) {
    try {
        $anvandarfragor = $forum->query(
            'SELECT anvandarnamn, namn FROM backenduser ORDER BY anvandarnamn'
        );
        $anvandare = $anvandarfragor->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $anvandarfel = 'Kunde inte lasa backendanvandarna fran databasen: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera backendanvandare</title>
</head>
<body>
    <h1>Hantera backendanvandare</h1>
    <?php if ($meddelande !== ''): ?>
        <p><?= htmlspecialchars($meddelande, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($databasfel !== ''): ?>
        <p>Databasfel: <?= htmlspecialchars($databasfel, ENT_QUOTES, 'UTF-8') ?></p>
        <?php if ($losenordskolumnForKort): ?>
            <p>Kor foljande SQL i databasen och forsok sedan skapa backendanvandaren igen:</p>
            <pre><code>ALTER TABLE backenduser MODIFY password VARCHAR(255);</code></pre>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($anvandarfel !== ''): ?>
        <p><?= htmlspecialchars($anvandarfel, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Anvandarnamn:</label>
        <input type="text" id="username" name="username" value="<?= htmlspecialchars($anvandarnamn, ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="name">Namn:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($namn, ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="password">Losenord:</label>
        <input type="password" id="password" name="password" autocomplete="new-password" required>
        <br>
        <button type="submit">Skapa backendanvandare</button>
    </form>
    <h2>Skapade backendanvandare</h2>
    <?php if (count($anvandare) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th scope="col">Anvandarnamn</th>
                    <th scope="col">Namn</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($anvandare as $enAnvandare): ?>
                    <tr>
                        <td><?= htmlspecialchars($enAnvandare['anvandarnamn'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($enAnvandare['namn'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Det finns inga backendanvandare annu.</p>
    <?php endif; ?>
    <p><a href="meny.php">Tillbaka till menyn</a></p>
</body>
</html>