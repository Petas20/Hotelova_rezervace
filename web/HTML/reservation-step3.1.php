<?php
session_start();

require "../../src/classes/Database.php";
$db = new Database();
$connection = $db->getConnection();

if (!isset($_SESSION["room_type"]) || !isset($_SESSION["date_from"]) || !isset($_SESSION["date_to"])) {
    header('Location: reservation-step1.1.php');
    exit;
}

// Získání názvu pokoje na základě ID pokoje v $_SESSION["room_name"]

$roomId = $_SESSION["room_name"];
$sql = "SELECT Nazev FROM POKOJ WHERE Id = :roomId";
$statement = $connection->prepare($sql);
$statement->bindParam(':roomId', $roomId);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);
$roomNameFromDatabase = $result["Nazev"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_confirm"])) {
    // Uložení rezervace do databáze

    $roomType = $_SESSION["room_type"];
    $roomName = $_SESSION["room_name"];
    $dateFrom = $_SESSION["date_from"];
    $dateTo = $_SESSION["date_to"];
    $name = $_SESSION["name"];
    $phone = $_SESSION["phone"];
    $email = $_SESSION["email"];

    // SQL dotaz pro vložení rezervace
    $sql = "INSERT INTO REZERVACE (Typ_pokoje, Cislo_pokoje, Datum_prijezdu, Datum_odjezdu, Jmeno, Telefon, Email) VALUES (:roomType, :roomName, :dateFrom, :dateTo, :name, :phone, :email)";

    $statement = $connection->prepare($sql);
    $statement->bindParam(':roomType', $roomType);
    $statement->bindParam(':roomName', $roomName);
    $statement->bindParam(':dateFrom', $dateFrom);
    $statement->bindParam(':dateTo', $dateTo);
    $statement->bindParam(':name', $name);
    $statement->bindParam(':phone', $phone);
    $statement->bindParam(':email', $email);

    if ($statement->execute()) {
        // Vymazání session
        session_unset();
        session_destroy();

        header('Location: reservation-step4.1.php');
        exit;
    } else {
        echo "<p>Chyba při rezervaci.</p>";
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
    <link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">    <link href="https://fonts.googleapis.com/css2?family=Emblema+One&display=swap" rel="stylesheet">

    <title>Rezervační formulář - Potvrzení rezervace</title>
</head>
<body>
    <?php require "../../src/assets/header.php"?>
    <main>
        <section class="form">
            <h2>Potvrzení rezervace</h2>
            <?php
                if (isset($_SESSION["room_type"])) {
                    echo "<p>Datum příjezdu: " . date("d.m.Y", strtotime($_SESSION["date_from"])) . "</p>";
                    echo "<p>Datum odjezdu: " . date("d.m.Y", strtotime($_SESSION["date_to"])) . "</p>";
                    $roomTypes = array(
                        "1" => "3 - Štěnice",
                        "2" => "2 - Bez štěnic",
                        "3" => "1 - Bez štěnic s ramínky"
                    );
                    $selectedRoomType = $roomTypes[$_SESSION["room_type"]];
                    echo "<p>Kategorie pokoje: " . htmlspecialchars($selectedRoomType) . "</p>";
                    echo "<p>Pokoj: " . htmlspecialchars($roomNameFromDatabase) . "</p>";          
                    echo "<p>Jméno: " . htmlspecialchars($_SESSION["name"]) . "</p>";
                    echo "<p>Telefon: " . htmlspecialchars($_SESSION["phone"]) . "</p>";
                    echo "<p>E-mail: " . htmlspecialchars($_SESSION["email"]) . "</p>";
            }
            ?>
            <form method="post">
                <button type="submit" name="submit_confirm">REZERVOVAT</button>
                <button type="button"><a class="back" href="reservation-step2.1.php">Změnit</a></button>
            </form>
        </section>
    </main>
    <?php require "../../src/assets/footer.php"?>    
</body>
</html>
