
<?php
session_start();
require "../../src/classes/Database.php";

// Zpracování dat z první části formuláře
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_part1"])) {
    // Získání dat z první části formuláře
    $dateFrom = $_POST["date_from"];
    $dateTo = $_POST["date_to"];
    $roomType = $_POST["room_type"];

    // Přidaní kontroly, zda Datum odjezdu je platné
    if (strtotime($dateFrom) > strtotime($dateTo)) {
        $messagePastError = "<p class='error'>Byla zadána cesta do minulosti, to zatím není možné.</p>";
    }
    elseif (strtotime($dateFrom) === strtotime($dateTo)) {
        $messageSameDateError = "<p class='error'>Datum odjezdu bohužel nemůže být ve stejný den jako datum příjezdu.</p>";
    } 
    else {
        // Uložení dat do session proměnných
        $_SESSION["date_from"] = $dateFrom;
        $_SESSION["date_to"] = $dateTo;
        $_SESSION["room_type"] = $roomType;

        // Přesměrování na druhý krok
        header('Location: reservation-step2.1.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/form.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">    
    <link href="https://fonts.googleapis.com/css2?family=Emblema+One&display=swap" rel="stylesheet">

<title>Rezervační formulář - krok 1</title>
</head>
<body>
    <?php require "../../src/assets/header.php"?>
    <main>
        <section class="form">
            <h2>Rezervace</h2>
            <form method="post">
                <label for="date_from">Datum příjezdu</label>
                <br>
                <input type="date" id="date_from" name="date_from" min="<?php echo date('Y-m-d'); ?>" required>
                <br>
                <label for="date_to">Datum odjezdu</label>
                <br>
                <input type="date" id="date_to" name="date_to" min="<?php echo date('Y-m-d'); ?>" required>
                <br>
                <label for="room_type">Kategorie pokoje</label>
                <br>
                <select id="room_type" name="room_type">
                    <option value="1">3 - Štěnice </option>
                    <option value="2">2 - Bez štěnic</option>
                    <option value="3">1 - Bez štěnic s ramínky</option>
                </select>
                <br>
                <button type="submit" name="submit_part1">Vybrat</button>
            </form>
                <?php 
                    if(isset($messagePastError)){
                        echo $messagePastError;
                    }
                    if(isset($messageSameDateError)){
                        echo $messageSameDateError;
                    }
                ?>
        </section>
    </main>
    <?php require "../../src/assets/footer.php"?>    
</body>
</html>
