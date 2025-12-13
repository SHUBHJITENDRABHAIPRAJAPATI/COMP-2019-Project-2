<?php
//adding db connection and header
include "db-conn.php";
include "templates/header.php";

//get today's date
$today = date("Y-m-d");

// fetching all states for the dropdown(from states table)
$stmt = $pdo->prepare("SELECT id, name FROM states ORDER BY name");
$stmt->execute();
$states = $stmt->fetchAll();

//displaying the search form
?>







<h2>Search For Listings here</h2>

<form action="results.php" method="get">
  <label for="state_id">State:</label>
  <select id="state_id" name="state_id" required>
    <option value="">choose-state</option>
    <?php foreach ($states as $s) { ?>
      <option value="<?php echo $s['id']; ?>"><?php echo $s['name']; ?></option>
    <?php } ?>
  </select>
  <br><br>

  <label for="checkin">Check-in:</label>

  <input type="date" id="checkin" name="checkin" min="<?php echo $today; ?>" required>

  <br>
  <br>

  <label for="checkout">Check-out:</label>
  <input type="date" id="checkout" name="checkout" min="<?php echo $today; ?>" required>

  <br>
  <br>

  <button type="submit">Search</button>
  
</form>

<p><strong>Note:</strong>Please,makesure checking date must be today or later, and check-out date must be after check-in date.</p>

<?php include "templates/footer.php"; ?>
