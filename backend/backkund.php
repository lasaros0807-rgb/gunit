<?php
$meddelande = '';
$databasfel = '';
$kundfel = '';
$redigeraKundId = '';
$kunder = [];

$kunduppgifter = [
    'fornamn' => '',
    'efternamn' => '',
    'email' => '',
    'telefon' => '',
    'losenord' => '',
    'adress' => '',
];

function skrivSaker($text)
{
    echo htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['kund_id'])) {
            $redigeraKundId = trim($_POST['kund_id']);
        }

        foreach ($kunduppgifter as $namn => $varde) {
            if (isset($_POST[$namn])) {
                $kunduppgifter[$namn] = trim($_POST[$namn]);
            }
        }

        $saknarUppgifter = false;
        if ($kunduppgifter['fornamn'] === '' ||
            $kunduppgifter['efternamn'] === '' ||
            $kunduppgifter['email'] === '' ||
            $kunduppgifter['telefon'] === '' ||
            $kunduppgifter['adress'] === '') {
            $saknarUppgifter = true;
        }

        if ($saknarUppgifter || ($redigeraKundId === '' && $kunduppgifter['losenord'] === '')) {
            $meddelande = 'Fyll i alla kunduppgifter.';
        } elseif (!filter_var($kunduppgifter['email'], FILTER_VALIDATE_EMAIL)) {
            $meddelande = 'Ange en giltig e-postadress.';
        } elseif ($redigeraKundId !== '' &&
                  (!ctype_digit($redigeraKundId) || (int) $redigeraKundId < 1)) {
            $meddelande = 'Ogiltigt kund-ID.';
        } else {
            $kundData = [
                'fornamn' => $kunduppgifter['fornamn'],
                'efternamn' => $kunduppgifter['efternamn'],
                'email' => $kunduppgifter['email'],
                'telefon' => $kunduppgifter['telefon'],
                'adress' => $kunduppgifter['adress'],
            ];

            if ($redigeraKundId !== '') {
                $kundData['kund_id'] = $redigeraKundId;

                if ($kunduppgifter['losenord'] !== '') {
                    $kundData['losenord'] = password_hash($kunduppgifter['losenord'], PASSWORD_DEFAULT);
                    $sql = 'UPDATE kund SET
                        fornamn = :fornamn,
                        efternamn = :efternamn,
                        email = :email,
                        telefon = :telefon,
                        adress = :adress,
                        losenord = :losenord
                        WHERE kund_id = :kund_id';
                } else {
                    $sql = 'UPDATE kund SET
                        fornamn = :fornamn,
                        efternamn = :efternamn,
                        email = :email,
                        telefon = :telefon,
                        adress = :adress
                        WHERE kund_id = :kund_id';
                }

                $sparning = $forum->prepare($sql);
                $sparning->execute($kundData);
                $meddelande = 'Kunduppgifterna har uppdaterats.';
            } else {
                $kundData['losenord'] = password_hash($kunduppgifter['losenord'], PASSWORD_DEFAULT);
                $sql = 'INSERT INTO kund
                    (fornamn, efternamn, email, telefon, losenord, adress)
                    VALUES
                    (:fornamn, :efternamn, :email, :telefon, :losenord, :adress)';

                $sparning = $forum->prepare($sql);
                $sparning->execute($kundData);
                $meddelande = 'Kunden har skapats.';
            }

            $redigeraKundId = '';
            foreach ($kunduppgifter as $namn => $varde) {
                $kunduppgifter[$namn] = '';
            }
        }
    }
} catch (PDOException $fel) {
    $databasfel = 'Databasfel vid hantering av kunder: ' . $fel->getMessage();
}

if (isset($forum)) {
    try {
        $kundfragor = $forum->query(
            'SELECT kund_id, fornamn, efternamn, email, telefon, adress
             FROM kund
             ORDER BY kund_id DESC'
        );
        $kunder = $kundfragor->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $fel) {
        $kundfel = 'Kunde inte lasa kunderna fran databasen: ' . $fel->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera kunder</title>
</head>
<body>
    <h1>Hantera kunder</h1>

    <?php if ($meddelande !== ''): ?>
        <p><?php skrivSaker($meddelande); ?></p>
    <?php endif; ?>
    <?php if ($databasfel !== ''): ?>
        <p><?php skrivSaker($databasfel); ?></p>
    <?php endif; ?>
    <?php if ($kundfel !== ''): ?>
        <p><?php skrivSaker($kundfel); ?></p>
    <?php endif; ?>

    <h2>Skapa kund</h2>
    <form method="post" action="">
        <label for="ny-fornamn">Fornamn:</label>
        <input type="text" id="ny-fornamn" name="fornamn" value="<?php skrivSaker($kunduppgifter['fornamn']); ?>" required><br>

        <label for="ny-efternamn">Efternamn:</label>
        <input type="text" id="ny-efternamn" name="efternamn" value="<?php skrivSaker($kunduppgifter['efternamn']); ?>" required><br>

        <label for="ny-email">E-post:</label>
        <input type="email" id="ny-email" name="email" value="<?php skrivSaker($kunduppgifter['email']); ?>" required><br>

        <label for="ny-telefon">Telefon:</label>
        <input type="tel" id="ny-telefon" name="telefon" value="<?php skrivSaker($kunduppgifter['telefon']); ?>" required><br>

        <label for="ny-losenord">Losenord:</label>
        <input type="password" id="ny-losenord" name="losenord" required><br>

        <label for="ny-adress">Adress:</label>
        <input type="text" id="ny-adress" name="adress" value="<?php skrivSaker($kunduppgifter['adress']); ?>" required><br>

        <button type="submit">Skapa kund</button>
    </form>

    <h2>Kunder i databasen</h2>
    <?php if (count($kunder) > 0): ?>
        <div class="kund-lista">
            <?php foreach ($kunder as $kund): ?>
                <?php
                $visa = $kund;
                if ((string) $kund['kund_id'] === $redigeraKundId) {
                    $visa = $kunduppgifter;
                    $visa['kund_id'] = $kund['kund_id'];
                }
                ?>
                <section class="kund-box">
                    <h3><?php skrivSaker($visa['fornamn'] . ' ' . $visa['efternamn']); ?></h3>
                    <p>Kund-ID: <?php skrivSaker($kund['kund_id']); ?></p>

                    <form method="post" action="">
                        <input type="hidden" name="kund_id" value="<?php skrivSaker($kund['kund_id']); ?>">

                        <label for="fornamn-<?php skrivSaker($kund['kund_id']); ?>">Fornamn:</label>
                        <input type="text" id="fornamn-<?php skrivSaker($kund['kund_id']); ?>" name="fornamn" value="<?php skrivSaker($visa['fornamn']); ?>" required><br>

                        <label for="efternamn-<?php skrivSaker($kund['kund_id']); ?>">Efternamn:</label>
                        <input type="text" id="efternamn-<?php skrivSaker($kund['kund_id']); ?>" name="efternamn" value="<?php skrivSaker($visa['efternamn']); ?>" required><br>

                        <label for="email-<?php skrivSaker($kund['kund_id']); ?>">E-post:</label>
                        <input type="email" id="email-<?php skrivSaker($kund['kund_id']); ?>" name="email" value="<?php skrivSaker($visa['email']); ?>" required><br>

                        <label for="telefon-<?php skrivSaker($kund['kund_id']); ?>">Telefon:</label>
                        <input type="tel" id="telefon-<?php skrivSaker($kund['kund_id']); ?>" name="telefon" value="<?php skrivSaker($visa['telefon']); ?>" required><br>

                        <label for="losenord-<?php skrivSaker($kund['kund_id']); ?>">Losenord:</label>
                        <input type="password" id="losenord-<?php skrivSaker($kund['kund_id']); ?>" name="losenord" value=""><br>

                        <label for="adress-<?php skrivSaker($kund['kund_id']); ?>">Adress:</label>
                        <input type="text" id="adress-<?php skrivSaker($kund['kund_id']); ?>" name="adress" value="<?php skrivSaker($visa['adress']); ?>" required><br>

                        <p>Lamna losenordet tomt om det inte ska andras.</p>
                        <button type="submit">Spara andringar</button>
                    </form>
                </section>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Det finns inga kunder i databasen annu.</p>
    <?php endif; ?>

    <p><a href="meny.php">Tillbaka till menyn</a></p>
</body>
</html>
