<?php
$server = "localhost";
$user = "root";
$pw = "";
$db = "hotel";

$conn = new mysqli($server, $user, $pw, $db);
if ($conn->connect_error) {
    die("probleme de connexion : ". $conn->connect_error);
}

?>