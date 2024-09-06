<?php
//If we include this file in any other file then we will be able to connect to the database and will be able to use $conn variable(without even initialising it in that file) to interact with the db.
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'mapdb';

//To establish a connection with the mysql database
$conn = new mysqli($host, $username, $password, $database);

//Once the connection is established, the $conn variable can be used to interact with the database, such as executing queries and retrieving results.
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>