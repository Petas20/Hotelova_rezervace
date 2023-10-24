
<?php
session_start();
require "../../src/classes/Database.php";

// Kontrola dostupných pokojů na základě výběru typu pokoje
if (isset($_SESSION["room_type"])) {
    $db = new Database();
    $connection = $db->getConnection();

    // SQL dotaz pro získání dostupných pokojů daného typu v daném období
    $sql = "SELECT Id, Nazev 
            FROM POKOJ 
            WHERE Typ = :roomType AND Id NOT IN 
                (SELECT Cislo_pokoje 
                FROM REZERVACE 
                WHERE (:dateTo >= Datum_prijezdu AND :dateFrom < Datum_odjezdu)
                )";

    // Provedení SQL dotazu
    $statement = $connection->prepare($sql);
    $statement->bindParam(':roomType', $_SESSION["room_type"]);
    $statement->bindParam(':dateFrom', $_SESSION["date_from"]);
    $statement->bindParam(':dateTo', $_SESSION["date_to"]);
    $statement->execute();
    // Získání výsledků dotazu
    $availableRooms = $statement->fetchAll(PDO::FETCH_ASSOC);
    
    // Pokud nejsou k dispozici žádné pokoje - informování uživatele.
    if (isset($availableRooms) && empty($availableRooms)) {

        // Určení nejbližšího dostupného data pro vybraný typ pokoje
        $sql = "SELECT MIN(Datum_odjezdu) AS nejblizsi_datum
                FROM REZERVACE
                WHERE Datum_odjezdu >= :dateFrom 
                    AND Typ_pokoje = :roomType
        ";

        $statement = $connection->prepare($sql);
        $statement->bindParam(':roomType', $_SESSION["room_type"]);
        $statement->bindParam(':dateFrom', $_SESSION["date_from"]);
        $statement->execute();
    
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        $nejblizsiDatum = $result["nejblizsi_datum"];
    }
}
// Zpracovaní dat z druhé části formuláře (rezervace)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_part2"])) {
    // Získání dat z druhé části formuláře
    $roomName = isset($_POST["room_name"]) ? $_POST["room_name"] : null;
    $name = isset($_POST["name"]) ? $_POST["name"] : null;
    $phone = isset($_POST["phone"]) ? $_POST["phone"] : null;
    $email = isset($_POST["email"]) ? $_POST["email"] : null;

// Uložení dat do session proměnných, pouze pokud byla zadána
    if ($roomName !== null) {
        $_SESSION["room_name"] = $roomName;
    }
    if ($name !== null) {
        $_SESSION["name"] = $name;
    }
    if ($phone !== null) {
        $_SESSION["phone"] = $phone;
    }
    if ($email !== null) {
        $_SESSION["email"] = $email;
    }
    // Přesměrování na třetí krok
        if (isset($roomName) && !empty($roomName) && isset($name) && !empty($name) && isset($phone) && !empty($phone) && isset($email) && !empty($email)) {
            header('Location: reservation-step3.1.php');
            exit;
        }
        elseif (empty($roomName)){
            $message = "<p class='error'></br>Musíte si vybrat pokoj.</p>";
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
    
    <title>Rezervační formulář - krok 2</title>
</head>
<body>
    <?php require "../../src/assets/header.php"?>
        <main>
            <section class="form">
                <h2>Rezervace</h2>
                <?php
                    $roomTypes = array(
                        "1" => "3 - Štěnice",
                        "2" => "2 - Bez štěnic",
                        "3" => "1 - Bez štěnic s ramínky"
                        );
                if (isset($_SESSION["room_type"])) {
                    // Zobrazení textových dat z prvního formuláře nad druhým formulářem
                    echo "<p>Datum příjezdu: " . date("d.m.Y", strtotime($_SESSION["date_from"])) . "</p>";
                    echo "<p>Datum odjezdu: " . date("d.m.Y", strtotime($_SESSION["date_to"])) . "</p>";
                    // Získání názvu typu pokoje z asociativního pole
                    $selectedRoomType = $roomTypes[$_SESSION["room_type"]];
                    echo "<p>Kategorie pokoje: " . htmlspecialchars($selectedRoomType) . "</p>";
                    // Druhá část formuláře pro rezervaci
                    ?>
                <form method="post">
                    <label for="room_name">Vyberte si pokoj</label>
                    <br>
                    <?php
                        if (isset($availableRooms) && !empty($availableRooms)) {
                            echo '<select id="room_name" name="room_name">';
                            foreach ($availableRooms as $room) {
                                echo '<option value="' . htmlspecialchars($room["Id"]) . '">' . htmlspecialchars($room["Nazev"]) . '</option>';
                            }
                            echo '</select>';
                        }
                        else{
                            echo "</br>Pokoj v této kategorii bude dostupný nejdříve ".date("d.m.Y", strtotime($nejblizsiDatum)). 
                            "</br></br><a href='reservation-step1.1.php'>ZMĚNIT DATUM POBYTU NEBO TYP POKOJE.</a>
                            </br>";
                        }
                        ?>
                    <br>
                    <label for="name">Celé jméno</label>
                    <br>
                    <input type="text" id="name" name="name" required>
                    <br>
                    <label for="phone">Telefon</label>
                    <br>
                    <input type="text" inputmode="numeric" pattern="[+]?[0-9]*" id="phone" name="phone" required>
                    <br>
                    <label for="email">E-mail</label>
                    <br>
                    <input type="email" id="email" name="email" required>
                    <br>
                    <button type="submit" name="submit_part2">Potvrdit</button>
                    <button type="button"><a class="back" href="reservation-step1.1.php">ZPĚT</a></button>
                </form>
                <?php
                    if (isset($message)) {
                        echo "<p>$message</p>";
                    }
                ?>
                <?php
                }
                ?>
            </section>
        </main>
    <?php require "../../src/assets/footer.php"?>    
</body>
</html>
