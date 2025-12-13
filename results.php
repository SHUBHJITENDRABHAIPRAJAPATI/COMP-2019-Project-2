<?php
include "db-conn.php";
include "templates/header.php";

//read and validateee GET parameters

$state_id = $_GET["state_id"];
$checkin  = $_GET["checkin"];
$checkout = $_GET["checkout"];

$errors = array();//array to hold validation errors

//  validaation
if ($state_id == "")
{
    $errors[] = "State not provided.";
}

if ($checkin == "" || $checkout == "") {
    $errors[] = "Dates not provided.";
}

//checking in and checkout dates validation
$d1 = date_create($checkin);
$d2 = date_create($checkout);
$today = date_create(date("Y-m-d"));

//checking and checkout dates ensuring
if ($d1 && $d2) {
    if ($d1 < $today) {
        $errors[] = "Check-in cannot be earlier than today.";
    }
    if ($d2 <= $d1) {
        $errors[] = "Check-out must be after check-in.";
    }
} else {
    $errors[] = "Invalid dates.";
}
//if there are errors, display them and exit
if (!empty($errors)) {
    echo "<h2>Search error</h2>";
    foreach ($errors as $e) {
        echo "<p style='color:red;'>" . $e . "</p>";
    }
    echo "<form action='index.php' method='get'><button type='submit'>Change State or Dates</button></form>";
    include "templates/footer.php";
    exit;
}
//fetch cities in the selected state that have places
$sql = "SELECT DISTINCT c.id, c.name
        FROM cities c
        JOIN places p ON p.city_id = c.id
        WHERE c.state_id = :sid
        ORDER BY c.name";

$stmt = $pdo->prepare($sql);
$stmt->execute(['sid' => $state_id]);
$cities = $stmt->fetchAll();

//if no cities found in db, show-msg and back button
if (count($cities) == 0) {
    echo "no results";

    echo "<form action='index.php' method='get'><button type='submit'>Change State or Dates</button></form>";
    include "templates/footer.php";
    exit;
}


//display city dropdown, rooms, guests input and search button
echo "<p>Dates: " . $checkin . " to " . $checkout . "</p>";
echo "<form action='index.php' method='get'><button type='submit'>Change State or Dates</button></form>";
echo "<br><br>";

echo "<form method='post' action=''>";
echo "City: ";
echo "<select name='city_id'>";
echo "<option value=''>Choose city</option>";


//throwing cities in dropdown
foreach ($cities as $c) {
    echo "<option value='" . $c['id'] . "'>" . $c['name'] . "</option>";
}

echo "</select>";
echo "<br><br>";

echo "Rooms: <input type='number' name='rooms' value='1' min='1'>";
echo "<br><br>";

echo "Guests: <input type='number' name='guests' value='1' min='1'>";
echo "<br><br>";

echo "<input type='hidden' name='checkin' value='" . $checkin . "'>";
echo "<input type='hidden' name='checkout' value='" . $checkout . "'>";

echo "<button type='submit'>Search</button>";
echo "</form>";

echo "<hr>";
//process POST request for city, rooms, guests (p,r,g)

if ($_SERVER["REQUEST_METHOD"] == "POST") {// POST check as  well as validation

    $city_id = $_POST["city_id"];
    $rooms = $_POST["rooms"];
    $guests = $_POST["guests"];

    if ($rooms < 1) {
        $rooms = 1;
    }
    if ($guests < 1) {
        $guests = 1;
    }

    $places = array();

    if ($city_id != "") {
        //prepared statement with parameters for city, rooms, guests
        //  prepared query for fetching places as per p-2:criteria 
        $q = "SELECT p.*, u.first_name AS owner_first, u.last_name AS owner_last
              FROM places p
              JOIN users u ON u.id = p.user_id
              WHERE p.city_id = :cid
              AND p.number_rooms >= :r
              AND p.max_guest >= :g
              ORDER BY p.name";

        $st = $pdo->prepare($q);
        $st->execute(["cid" => $city_id, "r" => $rooms, "g" => $guests]);
        $places = $st->fetchAll();
    }

    if (count($places) == 0) 
    {
        echo "no results";
    } else {
        echo "<h3>Available listings</h3>";

        //displaying  places with book button
        foreach ($places as $p) {
            echo "<div class='listing'>";

            echo "<strong>" . $p["name"] . "</strong><br>";
            echo $p["description"] . "<br>";

            echo "Rooms: " . $p["number_rooms"] . " | ";
            echo "Guests: " . $p["max_guest"] . " | ";
            echo "Price/Night: " . $p["price_by_night"] . "<br>";
            echo "Owner: " . $p["owner_first"] . " " . $p["owner_last"] . "<br><br>";



            //book form with hidden inputs for place_id, checkin, checkout, rooms, guests
            
            echo "<form method='post' action='book.php'>";
            
            echo "<input type='hidden' name='place_id' value='" . $p["id"] . "'>";//place id->for booking
            echo "<input type='hidden' name='checkin' value='" . $checkin . "'>";//checkin-> for cheking date
            echo "<input type='hidden' name='checkout' value='" . $checkout . "'>";//checkout: for checkout date
            echo "<input type='hidden' name='rooms' value='" . $rooms . "'>";//rooms
            echo "<input type='hidden' name='guests' value='" . $guests . "'>";//guests
            echo "<button type='submit'>Book</button>";
            echo "</form>";

            echo "</div>";
        }
    }
}

include "templates/footer.php";
?>
