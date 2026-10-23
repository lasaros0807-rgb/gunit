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
$bilar = [];

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        } elseif (!ctype_digit($biluppgifter['arsmodell']) || !is_numeric($biluppgifter['pris']) || !is_numeric($biluppgifter['mil'])) {
            $meddelande = 'Arsmodell maste vara ett heltal och pris samt mil maste vara numeriska varden.';
        } else {
            $sparning = $forum->prepare(
                'INSERT INTO bil (`marke`, `modell`, `arsmodell`, `pris`, `mil`, `drivmedel`, `vaxellada`, `farg`, `status`, `bild_url`)
                 VALUES (:marke, :modell, :arsmodell, :pris, :mil, :drivmedel, :vaxellada, :farg, :status, :bild_url)'
            );
            $sparning->execute([
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
            ]);
            $meddelande = 'Bilen har skapats.';
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
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hantera bilar</title>
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
        <table>
            <thead>
                <tr>
                    <th scope="col">Bil-ID</th>
                    <th scope="col">Marke</th>
                    <th scope="col">Modell</th>
                    <th scope="col">Arsmodell</th>
                    <th scope="col">Pris</th>
                    <th scope="col">Mil</th>
                    <th scope="col">Drivmedel</th>
                    <th scope="col">Vaxellada</th>
                    <th scope="col">Farg</th>
                    <th scope="col">Status</th>
                    <th scope="col">Bild-URL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bilar as $bil): ?>
                    <tr>
                        <?php foreach (['bil_id', 'marke', 'modell', 'arsmodell', 'pris', 'mil', 'drivmedel', 'vaxellada', 'farg', 'status', 'bild_url'] as $kolumn): ?>
                            <td><?= htmlspecialchars((string) ($bil[$kolumn] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Det finns inga bilar i databasen annu.</p>
    <?php endif; ?>
    <p><a href="meny.php">Tillbaka till menyn</a></p>
</body>
</html>