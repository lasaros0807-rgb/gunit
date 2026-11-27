<?php
$meddelande = '';
$databasfel = '';
$anvandarfel = '';
$losenordskolumnForKort = false;
$anvandarnamn = '';
$namn = '';
$anvandare = [];

function skrivSaker($text)
{
    echo htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $anvandarnamn = trim($_POST['username'] ?? '');
        $namn = trim($_POST['name'] ?? '');
        $losenord = $_POST['password'] ?? '';

        if ($anvandarnamn === '' || $namn === '' || $losenord === '') {
            $meddelande = 'Fyll i anvandarnamn, namn och losenord.';
        } else {
            $kontroll = $forum->prepare(
                'SELECT 1 FROM backenduser WHERE anvandarnamn = :anvandarnamn'
            );
            $kontroll->execute(['anvandarnamn' => $anvandarnamn]);

            if ($kontroll->fetchColumn()) {
                $meddelande = 'Anvandarnamnet anvands redan.';
            } else {
                $sparning = $forum->prepare(
                    'INSERT INTO backenduser (anvandarnamn, namn, password)
                     VALUES (:anvandarnamn, :namn, :losenord)'
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
} catch (PDOException $fel) {
    if ($fel->getCode() === '22001') {
        $databasfel = 'Losenordskolumnen ar for kort. Andra den till VARCHAR(255) i databasen.';
        $losenordskolumnForKort = true;
    } else {
        $databasfel = 'Databasfel vid skapande av backendanvandare: ' . $fel->getMessage();
    }
}

if (isset($forum)) {
    try {
        $anvandarfragor = $forum->query(
            'SELECT anvandarnamn, namn FROM backenduser ORDER BY anvandarnamn'
        );
        $anvandare = $anvandarfragor->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $fel) {
        $anvandarfel = 'Kunde inte lasa backendanvandarna fran databasen: ' . $fel->getMessage();
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
        <p><?php skrivSaker($meddelande); ?></p>
    <?php endif; ?>
    <?php if ($databasfel !== ''): ?>
        <p><?php skrivSaker($databasfel); ?></p>
        <?php if ($losenordskolumnForKort): ?>
            <pre><code>ALTER TABLE backenduser MODIFY password VARCHAR(255);</code></pre>
        <?php endif; ?>
    <?php endif; ?>
    <?php if ($anvandarfel !== ''): ?>
        <p><?php skrivSaker($anvandarfel); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="username">Anvandarnamn:</label>
        <input type="text" id="username" name="username" value="<?php skrivSaker($anvandarnamn); ?>" required>
        <br>

        <label for="name">Namn:</label>
        <input type="text" id="name" name="name" value="<?php skrivSaker($namn); ?>" required>
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
                        <td><?php skrivSaker($enAnvandare['anvandarnamn']); ?></td>
                        <td><?php skrivSaker($enAnvandare['namn']); ?></td>
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
