<?php
    $host = "127.0.0.1";
    $user = "root";
    $password = "";
    $dbname = "furniland_db";

    $connection = mysqli_connect($host, $user, $password, $dbname);

    if(!$connection) {
        die("Connection failed: " . mysqli_connect_error());
    }
?>