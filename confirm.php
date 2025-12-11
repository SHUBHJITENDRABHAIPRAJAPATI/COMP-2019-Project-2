<?php
include "db-conn.php";
include "templates/header.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p>Invalid access. <a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

$place_id = isset($_POST['place_id']) ? $_POST['place_id'] : '';
$checkin  = isset($_POST['checkin']) ? $_POST['checkin'] : '';
$checkout = isset($_POST['checkout']) ? $_POST['checkout'] : '';
$rooms    = isset($_POST['rooms']) ? (int)$_POST['rooms'] : 1;
$guests   = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;

// checking required fields,if missing show error and back to home as well
if ($place_id == '')
{
    echo "<p>missing info. <a href='index.php'>Back</a></p>";
    include "templates/footer.php";
    exit;
}

$st = $pdo->prepare("SELECT name FROM places WHERE id = :id LIMIT 1");
$st->execute(array('id' => $place_id));
$row = $st->fetch();
$placeName = $row ? $row['name'] : "Selected listing";// fetch place name for confirmation

//displaying output
echo "<p>Your booking for <strong>" . $placeName . "</strong> is confirmed.</p>";
echo "<p>Check-in: " . $checkin . " | Check-out: " . $checkout . "</p>";
echo "<p>Rooms: " . $rooms . " | Guests: " . $guests . "</p>";
echo "<p><a href='index.php'>Back to Home</a></p>";

include "templates/footer.php";
?>
