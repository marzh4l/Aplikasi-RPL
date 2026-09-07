<?php
    define('BASE_URL', 'http://localhost/rpl-konversi/');
    // Tambahkan di awal file untuk debug
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../error.log');

    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_rpl";

    $conn = new mysqli($host, $user, $pass, $db);
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
?>