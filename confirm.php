<?php
include "db-conn.php";
include "templates/header.php";

//reading data from POST request(place_id, checkin, checkout, rooms, guests)
$place_id=$_POST["place_id"];
$checkin = $_POST["checkin"];
$checkout= $_POST["checkout"];
$rooms= $_POST["rooms"];
$guests= $_POST["guests"];

// required check as usual from above book.php
if ($place_id == "") {
    echo "<p>Missing info. <a href='index.php'>Back</a></p>";
    include "templates/footer.php";
    exit;
}

// fetch place name for confirmation display
$st = $pdo->prepare("SELECT name FROM places WHERE id = :id LIMIT 1");
$st->execute(array("id" => $place_id));
$row = $st->fetch();
if ($row) {
    $placeName = $row["name"];
} else {
    $placeName = "Selected listing";
}

// output for the confirmation page
echo "<p>Your booking for <strong>" . $placeName . "</strong> is confirmed.</p>";
echo "<p>Check-in: " . $checkin . " | Check-out: " . $checkout . "</p>";
echo "<p>Rooms: " . $rooms . " | Guests: " . $guests . "</p>";
echo "<p><a href='index.php'>Back to Home</a></p>";

include "templates/footer.php";
?>
