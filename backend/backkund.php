<?php
$meddelande = '';
$databasfel = '';
$kundfel = '';
$kunduppgifter = [
    'fornamn' => '',
    'efternamn' => '',
    'email' => '',
    'telefon' => '',
    'losenord' => '',
    'adress' => '',
];
$redigeraKundId = '';
$kunder = [];

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $redigeraKundId = trim($_POST['kund_id'] ?? '');
        foreach ($kunduppgifter as $falt => $varde) {
            $kunduppgifter[$falt] = trim($_POST[$falt] ?? '');
        }

        $saknarUppgifter = false;
        foreach (['fornamn', 'efternamn', 'email', 'telefon', 'adress'] as $falt) {
            if ($kunduppgifter[$falt] === '') {
                $saknarUppgifter = true;
                break;
            }
        }

        if ($saknarUppgifter || ($redigeraKundId === '' && $kunduppgifter['losenord'] === '')) {
            $meddelande = 'Fyll i alla kunduppgifter.';
        } elseif (!filter_var($kunduppgifter['email'], FILTER_VALIDATE_EMAIL)) {
            $meddelande = 'Ange en giltig e-postadress.';
        } elseif ($redigeraKundId !== '' && (!ctype_digit($redigeraKundId) || (int) $redigeraKundId < 1)) {
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
                    $sparning = $forum->prepare(
                        'UPDATE kund SET fornamn = :fornamn, efternamn = :efternamn, email = :email,
                         telefon = :telefon, adress = :adress, losenord = :losenord WHERE kund_id = :kund_id'
                    );
                } else {
                    $sparning = $forum->prepare(
                        'UPDATE kund SET fornamn = :fornamn, efternamn = :efternamn, email = :email,
                         telefon = :telefon, adress = :adress WHERE kund_id = :kund_id'
                    );
                }
                $sparning->execute($kundData);
                $meddelande = 'Kunduppgifterna har uppdaterats.';
            } else {
                $kundData['losenord'] = password_hash($kunduppgifter['losenord'], PASSWORD_DEFAULT);
                $sparning = $forum->prepare(
                    'INSERT INTO kund (fornamn, efternamn, email, telefon, losenord, adress)
                     VALUES (:fornamn, :efternamn, :email, :telefon, :losenord, :adress)'
                );
                $sparning->execute($kundData);
                $meddelande = 'Kunden har skapats.';
            }

            $redigeraKundId = '';
            foreach ($kunduppgifter as $falt => $varde) {
                $kunduppgifter[$falt] = '';
            }
        }
    }
} catch (PDOException $fel) {
    $databasfel = 'Databasfel vid hantering av kunder: ' . $fel->getMessage();
}

if (isset($forum)) {
    try {
        $kundfragor = $forum->query(
            'SELECT kund_id, fornamn, efternamn, email, telefon, adress FROM kund ORDER BY kund_id DESC'
        );
        $kunder = $kundfragor->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $fel) {
        $kundfel = 'Kunde inte lasa kunderna fran databasen: ' . $fel->getMessage();
    }
}

$kundFaltetiketter = [
    'fornamn' => 'Fornamn',
    'efternamn' => 'Efternamn',
    'email' => 'E-post',
    'telefon' => 'Telefon',
    'losenord' => 'Losenord',
    'adress' => 'Adress',
];
$kundFaltTyper = [
    'email' => 'email',
    'telefon' => 'tel',
    'losenord' => 'password',
];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera kunder</title>
    <style>
        .kund-lista { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
        .kund-box { min-width: 0; border: 1px solid #999; padding: 0.75rem; }
        .kund-falt { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; }
        .kund-falt label { display: block; margin-bottom: 0.25rem; }
        .kund-falt input { box-sizing: border-box; width: 100%; }
        .kund-box button { margin-top: 1rem; }
        @media (max-width: 900px) {
            .kund-lista { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 600px) {
            .kund-lista, .kund-falt { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <h1>Hantera kunder</h1>
    <?php if ($meddelande !== ''): ?>
        <p><?= htmlspecialchars($meddelande, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($databasfel !== ''): ?>
        <p><?= htmlspecialchars($databasfel, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($kundfel !== ''): ?>
        <p><?= htmlspecialchars($kundfel, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <h2>Skapa kund</h2>
    <form method="post" action="">
        <?php foreach ($kundFaltetiketter as $falt => $etikett): ?>
            <?php $typ = $kundFaltTyper[$falt] ?? 'text'; ?>
            <label for="ny-<?= $falt ?>"><?= $etikett ?>:</label>
            <input type="<?= $typ ?>" id="ny-<?= $falt ?>" name="<?= $falt ?>" value="<?= $falt === 'losenord' ? '' : htmlspecialchars($kunduppgifter[$falt], ENT_QUOTES, 'UTF-8') ?>" required>
            <br>
        <?php endforeach; ?>
        <button type="submit">Skapa kund</button>
    </form>

    <h2>Kunder i databasen</h2>
    <?php if (count($kunder) > 0): ?>
        <div class="kund-lista">
            <?php foreach ($kunder as $kund): ?>
                <?php
                $kundAttVisa = (string) $kund['kund_id'] === $redigeraKundId
                    ? array_merge($kund, $kunduppgifter)
                    : $kund;
                ?>
                <section class="kund-box">
                    <h3><?= htmlspecialchars($kundAttVisa['fornamn'] . ' ' . $kundAttVisa['efternamn'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p>Kund-ID: <?= htmlspecialchars((string) $kund['kund_id'], ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="kund_id" value="<?= htmlspecialchars((string) $kund['kund_id'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="kund-falt">
                            <?php foreach ($kundFaltetiketter as $falt => $etikett): ?>
                                <?php $typ = $kundFaltTyper[$falt] ?? 'text'; ?>
                                <div>
                                    <label for="<?= $falt ?>-<?= htmlspecialchars((string) $kund['kund_id'], ENT_QUOTES, 'UTF-8') ?>"><?= $etikett ?>:</label>
                                    <input type="<?= $typ ?>" id="<?= $falt ?>-<?= htmlspecialchars((string) $kund['kund_id'], ENT_QUOTES, 'UTF-8') ?>" name="<?= $falt ?>" value="<?= htmlspecialchars((string) ($kundAttVisa[$falt] ?? ''), ENT_QUOTES, 'UTF-8') ?>"<?= $falt === 'losenord' ? '' : ' required' ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p>Lämna lösenordet tomt om det inte ska ändras.</p>
                        <button type="submit">Spara ändringar</button>
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
