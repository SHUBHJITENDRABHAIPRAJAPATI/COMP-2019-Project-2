<?php
include "db-conn.php";
include "templates/header.php";

// get inputs from index.php (GET)
$state_id = isset($_GET['state_id']) ? $_GET['state_id'] : '';
$checkin  = isset($_GET['checkin']) ? $_GET['checkin'] : '';
$checkout = isset($_GET['checkout']) ? $_GET['checkout'] : '';

// simple validation for required values
$errors = array();
if ($state_id == '') $errors[] = "State not provided.";
if ($checkin == '' || $checkout == '') $errors[] = "Dates not provided.";

// simple date validation (lecture-level)
if (empty($errors)) {
    $d1 = date_create($checkin);
    $d2 = date_create($checkout);
    $today = date_create(date("Y-m-d"));
    //ensuring valid dates
    if (!$d1 || !$d2) {
        $errors[] = "Invalid dates.";
    } 
    else 
    {
        if ($d1 < $today) $errors[] = "Check-in cannot be earlier than today.";
        if ($d2 <= $d1) $errors[] = "Check-out must be after check-in.";
    }
}

// if errors, show and allow change
if (!empty($errors)) {
    echo "<h2>Search error</h2>";
    foreach ($errors as $e) { echo "<div style='color:red'>" . $e . "</div>"; }
    echo "<form method='get' action='index.php'><button type='submit'>Change State or Dates</button></form>";
    include "templates/footer.php";
    exit;
}

// fetch cities that have places in this state (plain SQL)
$sql = "SELECT DISTINCT c.id, c.name
        FROM cities c
        JOIN places p ON p.city_id = c.id
        WHERE c.state_id = :state_id
        ORDER BY c.name";
$stmt = $pdo->prepare($sql);
$stmt->execute(array('state_id' => $state_id));
$cities = $stmt->fetchAll();

// checking if any cities found,if yes show no results
if (count($cities) == 0) {
    echo "no results";

    echo "<form method='get' action='index.php'><button type='submit'>Change State or Dates</button></form>";
    include "templates/footer.php";
    exit;
}

//showing selected state and dates
echo "<p>Dates: " . $checkin . " to " . $checkout . "</p>";
echo "<form method='get' action='index.php' style='display:inline;'><button type='submit'>Change State or Dates</button></form>";
echo "<br><br>";

//checking for cities and displaying dropdown
echo "<h2>Select City and Preferences</h2>";
echo "<form method='post' action=''>";
echo "<label for='city_id'>City:</label>";
echo "<select name='city_id' id='city_id' required>";
echo "<option value=''>--Choose city--</option>";
foreach ($cities as $c) {
    echo "<option value='" . $c['id'] . "'>" . $c['name'] . "</option>";
}
echo "</select>";
echo "<br><br>";

echo "<label for='rooms'>Number of rooms:</label>";
echo "<input type='number' id='rooms' name='rooms' min='1' value='1' required>";
echo "<br><br>";

echo "<label for='guests'>Number of guests:</label>";
echo "<input type='number' id='guests' name='guests' min='1' value='1' required>";
echo "<br><br>";

// so,addding hidden fields for checkin and checkout,so they are available on POST
echo "<input type='hidden' name='checkin' value='" . $checkin . "'>";
echo "<input type='hidden' name='checkout' value='" . $checkout . "'>";

echo "<button type='submit'>Search</button>";
echo "</form>";
echo "<hr>";

// if POST, process  form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $city_id = isset($_POST['city_id']) ? $_POST['city_id'] : '';//post city_id

    $rooms = isset($_POST['rooms']) ? (int)$_POST['rooms'] : 1;
    $guests = isset($_POST['guests']) ? (int)$_POST['guests'] : 1;

    // chck min
    if ($rooms < 1) $rooms = 1;
    if ($guests < 1) $guests = 1;

    // if city chosen, fetch all matching places
    $places = array();
    if ($city_id != '') {
        $q = "SELECT p.*, u.first_name AS owner_first, u.last_name AS owner_last
              FROM places p
              JOIN users u ON u.id = p.user_id
              WHERE p.city_id = :city_id
                AND p.number_rooms >= :rooms
                AND p.max_guest >= :guests
              ORDER BY p.name";
        $st = $pdo->prepare($q);
        $st->execute(array('city_id' => $city_id, 'rooms' => $rooms, 'guests' => $guests));
        $places = $st->fetchAll();
    }

    // if no matches, print text as usal
    if (count($places) == 0) {
        echo "no results";
    } else {
        echo "<h3>Available listings</h3>";
          foreach ($places as $p) {
            echo "<div class='listing'>";
            echo "<strong>" . $p['name'] . "</strong><br>";
            echo $p['description'] . "<br>";
            echo "Rooms: " . $p['number_rooms'] . " | ";
            echo "Guests: " . $p['max_guest'] . " | ";
            echo "Price/night: " . $p['price_by_night'] . "<br>";
            echo "Owner: " . $p['owner_first'] . " " . $p['owner_last'] . "<br><br>";

            // book button for each listing,if user want to book
            echo "<form method='post' action='book.php'>";
            echo "<input type='hidden' name='place_id' value='" . $p['id'] . "'>";
            echo "<input type='hidden' name='checkin' value='" . $checkin . "'>";
            echo "<input type='hidden' name='checkout' value='" . $checkout . "'>";
            echo "<input type='hidden' name='rooms' value='" . $rooms . "'>";
            echo "<input type='hidden' name='guests' value='" . $guests . "'>";
            echo "<button type='submit'>Book</button>";
            echo "</form>";

            echo "</div>";
        }
    }
}

include "templates/footer.php";
?>
