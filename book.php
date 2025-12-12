<?php
include "db-conn.php";
include "templates/header.php";

//getting POST data and validating
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo "<p>Invalid access. <a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

//read data from POST request(place_id, checkin, checkout, rooms, guests)
$place_id = $_POST['place_id'];
$checkin  = $_POST['checkin'];
$checkout = $_POST['checkout'];
$rooms    = $_POST['rooms'];
$guests   = $_POST['guests'];

//information chekkk as usall
if ($place_id == "" || $checkin == "" || $checkout == "") {
    echo "<p style='color:red'>Missing booking information.</p>";
    echo "<p><a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

//  query to get place and owner info
$sql = "SELECT p.*, u.first_name AS owner_first, u.last_name AS owner_last, u.email AS owner_email
        FROM places p JOIN users u ON u.id = p.user_id
        WHERE p.id = :id LIMIT 1";
$st = $pdo->prepare($sql);
$st->execute(array('id' => $place_id));
$place = $st->fetch();

// if not found
if (!$place) {
    echo "<p>Place not found. <a href='index.php'>Back to Home</a></p>";
    include "templates/footer.php";
    exit;
}

// cal number of nights 
$start = date_create($checkin);
$end = date_create($checkout);

$nights = 0;

if ($start && $end) 

{
    $difference = date_diff($start, $end);
    $nights = $difference->format('%a'); // total days count
}

// output booking summary
echo "<h2>" . $place['name'] . "</h2>";
echo "<p>" . $place['description'] . "</p>";
echo "<p>Owner: <a href='user.php?id=" . $place['user_id'] . "'>" . $place['owner_first'] . " " . $place['owner_last'] . "</a></p>";
echo "<p>Price per Night: " . $place['price_by_night'] . "</p>";
echo "<p>Check-in: " . $checkin . "</p>";
echo "<p>Check-out: " . $checkout . "</p>";
echo "<p>Number of nights: " . $nights . "</p>";
echo "<p>Rooms requested: " . $rooms . "</p>";
echo "<p>Guests: " . $guests . "</p>";

//shwing ckn,ckout,rooms,guests in confirm and back to home button
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
