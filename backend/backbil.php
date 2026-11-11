<?php
$meddelande = '';
$databasfel = '';
$bilfel = '';
$biluppgifter = [
    'marke' => '',
    'modell' => '',
    'arsmodell' => '',
    'pris' => '',
    'mil' => '',
    'drivmedel' => '',
    'vaxellada' => '',
    'farg' => '',
    'status' => '',
    'bild_url' => '',
];
$redigeraBilId = '';
$bilar = [];

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $redigeraBilId = trim($_POST['bil_id'] ?? '');
        foreach ($biluppgifter as $falt => $varde) {
            $biluppgifter[$falt] = trim($_POST[$falt] ?? '');
        }

        $obligatoriskaFalt = [
            'marke', 'modell', 'arsmodell', 'pris', 'mil',
            'drivmedel', 'vaxellada', 'farg', 'status',
        ];
        $saknarObligatorisktFalt = false;
        foreach ($obligatoriskaFalt as $falt) {
            if ($biluppgifter[$falt] === '') {
                $saknarObligatorisktFalt = true;
                break;
            }
        }

        if ($saknarObligatorisktFalt) {
            $meddelande = 'Fyll i alla biluppgifter utom bild-URL.';
        } elseif ($redigeraBilId !== '' && (!ctype_digit($redigeraBilId) || (int) $redigeraBilId < 1)) {
            $meddelande = 'Ogiltigt bil-ID.';
        } elseif (!ctype_digit($biluppgifter['arsmodell']) || !is_numeric($biluppgifter['pris']) || !is_numeric($biluppgifter['mil'])) {
            $meddelande = 'Arsmodell maste vara ett heltal och pris samt mil maste vara numeriska varden.';
        } else {
            $bilData = [
                'marke' => $biluppgifter['marke'],
                'modell' => $biluppgifter['modell'],
                'arsmodell' => $biluppgifter['arsmodell'],
                'pris' => $biluppgifter['pris'],
                'mil' => $biluppgifter['mil'],
                'drivmedel' => $biluppgifter['drivmedel'],
                'vaxellada' => $biluppgifter['vaxellada'],
                'farg' => $biluppgifter['farg'],
                'status' => $biluppgifter['status'],
                'bild_url' => $biluppgifter['bild_url'],
            ];

            if ($redigeraBilId !== '' && ctype_digit($redigeraBilId)) {
                $sparning = $forum->prepare(
                    'UPDATE bil SET `marke` = :marke, `modell` = :modell, `arsmodell` = :arsmodell,
                     `pris` = :pris, `mil` = :mil, `drivmedel` = :drivmedel, `vaxellada` = :vaxellada,
                     `farg` = :farg, `status` = :status, `bild_url` = :bild_url WHERE `bil_id` = :bil_id'
                );
                $bilData['bil_id'] = $redigeraBilId;
                $sparning->execute($bilData);
                $meddelande = 'Biluppgifterna har uppdaterats.';
            } else {
                $sparning = $forum->prepare(
                    'INSERT INTO bil (`marke`, `modell`, `arsmodell`, `pris`, `mil`, `drivmedel`, `vaxellada`, `farg`, `status`, `bild_url`)
                     VALUES (:marke, :modell, :arsmodell, :pris, :mil, :drivmedel, :vaxellada, :farg, :status, :bild_url)'
                );
                $sparning->execute($bilData);
                $meddelande = 'Bilen har skapats.';
            }
            $redigeraBilId = '';
            foreach ($biluppgifter as $falt => $varde) {
                $biluppgifter[$falt] = '';
            }
        }
    }
} catch (PDOException $fel) {
    $databasfel = 'Databasfel vid skapande av bil: ' . $fel->getMessage();
}

if (isset($forum)) {
    try {
        $bilfragor = $forum->query(
            'SELECT `bil_id`, `marke`, `modell`, `arsmodell`, `pris`, `mil`, `drivmedel`, `vaxellada`, `farg`, `status`, `bild_url`
             FROM bil ORDER BY `bil_id` DESC'
        );
        $bilar = $bilfragor->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $fel) {
        $bilfel = 'Kunde inte lasa bilarna fran databasen: ' . $fel->getMessage();
    }
}

$bilFaltetiketter = [
    'marke' => 'Marke',
    'modell' => 'Modell',
    'arsmodell' => 'Arsmodell',
    'pris' => 'Pris',
    'mil' => 'Mil',
    'drivmedel' => 'Drivmedel',
    'vaxellada' => 'Vaxellada',
    'farg' => 'Farg',
    'status' => 'Status',
    'bild_url' => 'Bild-URL',
];
$bilFaltTyper = [
    'arsmodell' => ['number', '1'],
    'pris' => ['number', 'any'],
    'mil' => ['number', 'any'],
];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera bilar</title>
    <style>
        .bil-lista { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.75rem; }
        .bil-box { min-width: 0; border: 1px solid #999; padding: 0.75rem; }
        .bil-falt { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.5rem; }
        .bil-falt label { display: block; margin-bottom: 0.25rem; }
        .bil-falt input { box-sizing: border-box; width: 100%; }
        .bil-box button { margin-top: 1rem; }
        @media (max-width: 900px) {
            .bil-lista { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 600px) {
            .bil-lista, .bil-falt { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <h1>Hantera bilar</h1>
    <?php if ($meddelande !== ''): ?>
        <p><?= htmlspecialchars($meddelande, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($databasfel !== ''): ?>
        <p><?= htmlspecialchars($databasfel, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($bilfel !== ''): ?>
        <p><?= htmlspecialchars($bilfel, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" action="">
        <label for="marke">Marke:</label>
        <input type="text" id="marke" name="marke" value="<?= htmlspecialchars($biluppgifter['marke'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="modell">Modell:</label>
        <input type="text" id="modell" name="modell" value="<?= htmlspecialchars($biluppgifter['modell'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="arsmodell">Arsmodell:</label>
        <input type="number" id="arsmodell" name="arsmodell" step="1" value="<?= htmlspecialchars($biluppgifter['arsmodell'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="pris">Pris:</label>
        <input type="number" id="pris" name="pris" step="any" value="<?= htmlspecialchars($biluppgifter['pris'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="mil">Mil:</label>
        <input type="number" id="mil" name="mil" step="any" value="<?= htmlspecialchars($biluppgifter['mil'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="drivmedel">Drivmedel:</label>
        <input type="text" id="drivmedel" name="drivmedel" value="<?= htmlspecialchars($biluppgifter['drivmedel'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="vaxellada">Vaxellada:</label>
        <input type="text" id="vaxellada" name="vaxellada" value="<?= htmlspecialchars($biluppgifter['vaxellada'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="farg">Farg:</label>
        <input type="text" id="farg" name="farg" value="<?= htmlspecialchars($biluppgifter['farg'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="<?= htmlspecialchars($biluppgifter['status'], ENT_QUOTES, 'UTF-8') ?>" required>
        <br>
        <label for="bild_url">Bild-URL:</label>
        <input type="text" id="bild_url" name="bild_url" value="<?= htmlspecialchars($biluppgifter['bild_url'], ENT_QUOTES, 'UTF-8') ?>">
        <br>
        <button type="submit">Skapa bil</button>
    </form>

    <h2>Bilar i databasen</h2>
    <?php if (count($bilar) > 0): ?>
        <div class="bil-lista">
            <?php foreach ($bilar as $bil): ?>
                <?php
                $bilAttVisa = (string) $bil['bil_id'] === $redigeraBilId
                    ? array_merge($bil, $biluppgifter)
                    : $bil;
                ?>
                <section class="bil-box">
                    <h3><?= htmlspecialchars($bilAttVisa['marke'] . ' ' . $bilAttVisa['modell'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <p>Bil-ID: <?= htmlspecialchars((string) $bil['bil_id'], ENT_QUOTES, 'UTF-8') ?></p>
                    <form method="post" action="">
                        <input type="hidden" name="bil_id" value="<?= htmlspecialchars((string) $bil['bil_id'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="bil-falt">
                            <?php foreach ($bilFaltetiketter as $falt => $etikett): ?>
                                <?php [$typ, $steg] = $bilFaltTyper[$falt] ?? ['text', '']; ?>
                                <div>
                                    <label for="<?= $falt ?>-<?= htmlspecialchars((string) $bil['bil_id'], ENT_QUOTES, 'UTF-8') ?>"><?= $etikett ?>:</label>
                                    <input type="<?= $typ ?>" id="<?= $falt ?>-<?= htmlspecialchars((string) $bil['bil_id'], ENT_QUOTES, 'UTF-8') ?>" name="<?= $falt ?>" value="<?= htmlspecialchars((string) ($bilAttVisa[$falt] ?? ''), ENT_QUOTES, 'UTF-8') ?>"<?= $steg !== '' ? ' step="' . $steg . '"' : '' ?><?= $falt !== 'bild_url' ? ' required' : '' ?>>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit">Spara ändringar</button>
                    </form>
                </section>
                <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>Det finns inga bilar i databasen annu.</p>
    <?php endif; ?>
    <p><a href="meny.php">Tillbaka till menyn</a></p>
</body>
</html>