<?php
$meddelande = '';
$databasfel = '';
$bilfel = '';
$bilar = [];
$redigeraBilId = '';

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

function skrivSaker($text)
{
    echo htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

try {
    require_once 'assets/db.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['bil_id'])) {
            $redigeraBilId = trim($_POST['bil_id']);
        }

        foreach ($biluppgifter as $namn => $varde) {
            if (isset($_POST[$namn])) {
                $biluppgifter[$namn] = trim($_POST[$namn]);
            }
        }

        $saknarFalt = false;
        foreach ($biluppgifter as $namn => $varde) {
            if ($namn !== 'bild_url' && $varde === '') {
                $saknarFalt = true;
            }
        }

        if ($saknarFalt) {
            $meddelande = 'Fyll i alla biluppgifter utom bild-URL.';
        } elseif ($redigeraBilId !== '' && (!ctype_digit($redigeraBilId) || (int) $redigeraBilId < 1)) {
            $meddelande = 'Ogiltigt bil-ID.';
        } elseif (!ctype_digit($biluppgifter['arsmodell']) ||
                  !is_numeric($biluppgifter['pris']) ||
                  !is_numeric($biluppgifter['mil'])) {
            $meddelande = 'Arsmodell maste vara ett heltal och pris samt mil maste vara numeriska varden.';
        } else {
            if ($redigeraBilId !== '') {
                $sql = 'UPDATE bil SET
                    marke = :marke,
                    modell = :modell,
                    arsmodell = :arsmodell,
                    pris = :pris,
                    mil = :mil,
                    drivmedel = :drivmedel,
                    vaxellada = :vaxellada,
                    farg = :farg,
                    status = :status,
                    bild_url = :bild_url
                    WHERE bil_id = :bil_id';

                $biluppgifter['bil_id'] = $redigeraBilId;
                $sparning = $forum->prepare($sql);
                $sparning->execute($biluppgifter);
                $meddelande = 'Biluppgifterna har uppdaterats.';
            } else {
                $sql = 'INSERT INTO bil
                    (marke, modell, arsmodell, pris, mil, drivmedel, vaxellada, farg, status, bild_url)
                    VALUES
                    (:marke, :modell, :arsmodell, :pris, :mil, :drivmedel, :vaxellada, :farg, :status, :bild_url)';

                $sparning = $forum->prepare($sql);
                $sparning->execute($biluppgifter);
                $meddelande = 'Bilen har skapats.';
            }

            $redigeraBilId = '';
            foreach ($biluppgifter as $namn => $varde) {
                if ($namn !== 'bil_id') {
                    $biluppgifter[$namn] = '';
                }
            }
            unset($biluppgifter['bil_id']);
        }
    }
} catch (PDOException $fel) {
    $databasfel = 'Databasfel: ' . $fel->getMessage();
}

if (isset($forum)) {
    try {
        $bilfragor = $forum->query(
            'SELECT bil_id, marke, modell, arsmodell, pris, mil,
                    drivmedel, vaxellada, farg, status, bild_url
             FROM bil
             ORDER BY bil_id DESC'
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
        <p><?php skrivSaker($meddelande); ?></p>
    <?php endif; ?>
    <?php if ($databasfel !== ''): ?>
        <p><?php skrivSaker($databasfel); ?></p>
    <?php endif; ?>
    <?php if ($bilfel !== ''): ?>
        <p><?php skrivSaker($bilfel); ?></p>
    <?php endif; ?>

    <form method="post" action="">
        <label for="marke">Marke:</label>
        <input type="text" id="marke" name="marke" value="<?php skrivSaker($biluppgifter['marke']); ?>" required><br>

        <label for="modell">Modell:</label>
        <input type="text" id="modell" name="modell" value="<?php skrivSaker($biluppgifter['modell']); ?>" required><br>

        <label for="arsmodell">Arsmodell:</label>
        <input type="number" id="arsmodell" name="arsmodell" step="1" value="<?php skrivSaker($biluppgifter['arsmodell']); ?>" required><br>

        <label for="pris">Pris:</label>
        <input type="number" id="pris" name="pris" step="any" value="<?php skrivSaker($biluppgifter['pris']); ?>" required><br>

        <label for="mil">Mil:</label>
        <input type="number" id="mil" name="mil" step="any" value="<?php skrivSaker($biluppgifter['mil']); ?>" required><br>

        <label for="drivmedel">Drivmedel:</label>
        <input type="text" id="drivmedel" name="drivmedel" value="<?php skrivSaker($biluppgifter['drivmedel']); ?>" required><br>

        <label for="vaxellada">Vaxellada:</label>
        <input type="text" id="vaxellada" name="vaxellada" value="<?php skrivSaker($biluppgifter['vaxellada']); ?>" required><br>

        <label for="farg">Farg:</label>
        <input type="text" id="farg" name="farg" value="<?php skrivSaker($biluppgifter['farg']); ?>" required><br>

        <label for="status">Status:</label>
        <input type="text" id="status" name="status" value="<?php skrivSaker($biluppgifter['status']); ?>" required><br>

        <label for="bild_url">Bild-URL:</label>
        <input type="text" id="bild_url" name="bild_url" value="<?php skrivSaker($biluppgifter['bild_url']); ?>"><br>

        <button type="submit">Skapa bil</button>
    </form>

    <h2>Bilar i databasen</h2>

    <?php if (count($bilar) > 0): ?>
        <div class="bil-lista">
            <?php foreach ($bilar as $bil): ?>
                <?php
                $visa = $bil;
                if ((string) $bil['bil_id'] === $redigeraBilId) {
                    $visa = $biluppgifter;
                    $visa['bil_id'] = $bil['bil_id'];
                }
                ?>
                <section class="bil-box">
                    <h3><?php skrivSaker($visa['marke'] . ' ' . $visa['modell']); ?></h3>
                    <p>Bil-ID: <?php skrivSaker($bil['bil_id']); ?></p>

                    <form method="post" action="">
                        <input type="hidden" name="bil_id" value="<?php skrivSaker($bil['bil_id']); ?>">

                        <label for="marke-<?php skrivSaker($bil['bil_id']); ?>">Marke:</label>
                        <input type="text" id="marke-<?php skrivSaker($bil['bil_id']); ?>" name="marke" value="<?php skrivSaker($visa['marke']); ?>" required><br>

                        <label for="modell-<?php skrivSaker($bil['bil_id']); ?>">Modell:</label>
                        <input type="text" id="modell-<?php skrivSaker($bil['bil_id']); ?>" name="modell" value="<?php skrivSaker($visa['modell']); ?>" required><br>

                        <label for="arsmodell-<?php skrivSaker($bil['bil_id']); ?>">Arsmodell:</label>
                        <input type="number" id="arsmodell-<?php skrivSaker($bil['bil_id']); ?>" name="arsmodell" step="1" value="<?php skrivSaker($visa['arsmodell']); ?>" required><br>

                        <label for="pris-<?php skrivSaker($bil['bil_id']); ?>">Pris:</label>
                        <input type="number" id="pris-<?php skrivSaker($bil['bil_id']); ?>" name="pris" step="any" value="<?php skrivSaker($visa['pris']); ?>" required><br>

                        <label for="mil-<?php skrivSaker($bil['bil_id']); ?>">Mil:</label>
                        <input type="number" id="mil-<?php skrivSaker($bil['bil_id']); ?>" name="mil" step="any" value="<?php skrivSaker($visa['mil']); ?>" required><br>

                        <label for="drivmedel-<?php skrivSaker($bil['bil_id']); ?>">Drivmedel:</label>
                        <input type="text" id="drivmedel-<?php skrivSaker($bil['bil_id']); ?>" name="drivmedel" value="<?php skrivSaker($visa['drivmedel']); ?>" required><br>

                        <label for="vaxellada-<?php skrivSaker($bil['bil_id']); ?>">Vaxellada:</label>
                        <input type="text" id="vaxellada-<?php skrivSaker($bil['bil_id']); ?>" name="vaxellada" value="<?php skrivSaker($visa['vaxellada']); ?>" required><br>

                        <label for="farg-<?php skrivSaker($bil['bil_id']); ?>">Farg:</label>
                        <input type="text" id="farg-<?php skrivSaker($bil['bil_id']); ?>" name="farg" value="<?php skrivSaker($visa['farg']); ?>" required><br>

                        <label for="status-<?php skrivSaker($bil['bil_id']); ?>">Status:</label>
                        <input type="text" id="status-<?php skrivSaker($bil['bil_id']); ?>" name="status" value="<?php skrivSaker($visa['status']); ?>" required><br>

                        <label for="bild_url-<?php skrivSaker($bil['bil_id']); ?>">Bild-URL:</label>
                        <input type="text" id="bild_url-<?php skrivSaker($bil['bil_id']); ?>" name="bild_url" value="<?php skrivSaker($visa['bild_url']); ?>"><br>

                        <button type="submit">Spara andringar</button>
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
