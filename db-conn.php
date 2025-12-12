<?php
//Shubh Prajapati's DB connection 
// database connection 
$host = "localhost";
$user = "root";
$pass = "";
$db = "comp2002";
$dns="mysql:host=$host;dbname=$db;";


// ensure that errors are reported by using try-catch block
try {
    // creating a new PDO instance
    $pdo = new PDO($dns, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database";
    

} catch (PDOException $error) {
    // handle connection errors
    echo(" connection failed: " . $error->getMessage());
}
?>