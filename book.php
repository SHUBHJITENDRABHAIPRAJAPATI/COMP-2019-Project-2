<?php
include "db-conn.php";
include "templates/header.php";

// only allow POST (simple check)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p>Invalid access. <a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

// read posted values (basic)
$place_id = isset($_POST['place_id']) ? $_POST['place_id'] : '';
$checkin  = isset($_POST['checkin']) ? $_POST['checkin'] : '';
$checkout = isset($_POST['checkout']) ? $_POST['checkout'] : '';
$rooms    = isset($_POST['rooms']) ? $_POST['rooms'] : '1';
$guests   = isset($_POST['guests']) ? $_POST['guests'] : '1';

// simple required check
if ($place_id == '' || $checkin == '' || $checkout == '') {
    echo "<p style='color:red'>Missing booking information.</p>";
    echo "<p><a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

// fetch place and owner (basic prepared query)
$sql = "SELECT p.*, u.first_name AS owner_first, u.last_name AS owner_last, u.email AS owner_email 
     FROM places p JOIN users u ON u.id = p.user_id WHERE p.id = :id LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute(array('id' => $place_id));
$place = $st->fetch();

// if not found show error and back to home
if (!$place) {
    echo "<p>Place not found. <a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

// compute nights simply using strtotime (basic arithmetic)
$ts1 = strtotime($checkin);
$ts2 = strtotime($checkout);
$nights = 0;

if ($ts1 && $ts2 && $ts2 > $ts1) {
    $nights = ($ts2 - $ts1) / 86400;
    $nights = (int)$nights;
}

// display booking info here from $place and posted data and computed nights
echo "<h2>" . $place['name'] . "</h2>";
echo "<p>" . $place['description'] . "</p>";
echo "<p>Owner: <a href='user.php?id=" . $place['user_id'] . "'>" . $place['owner_first'] . " " . $place['owner_last'] . "</a></p>";
echo "<p>Price per Night: " . $place['price_by_night'] . "</p>";
echo "<p>Check-in: " . $checkin . "</p>";
echo "<p>Check-out: " . $checkout . "</p>";
echo "<p>Number of nights: " . $nights . "</p>";
echo "<p>Rooms requested: " . $rooms . "</p>";
echo "<p>Guests: " . $guests . "</p>";

// confirm & back buttons for booking
echo "<form method='post' action='confirm.php' style='display:inline;'>";
echo "<input type='hidden' name='place_id' value='" . $place_id . "'>";
echo "<input type='hidden' name='checkin' value='" . $checkin . "'>";
echo "<input type='hidden' name='checkout' value='" . $checkout . "'>";
echo "<input type='hidden' name='rooms' value='" . $rooms . "'>";
echo "<input type='hidden' name='guests' value='" . $guests . "'>";
echo "<button type='submit'>Confirm Booking</button>";
echo "</form>";

echo "<form method='get' action='index.php' style='display:inline; margin-left:10px;'>";
echo "<button type='submit'>Back to Home</button>";
echo "</form>";

include "templates/footer.php";
?>
