<?php
// db_connection.php
$host = 'localhost';
$username = 'root'; // Sesuaikan dengan user database Anda
$password = '';     // Sesuaikan dengan password database Anda
$database = 'rsud_sanjiwani'; // Nama database

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    // Set error mode ke exception agar mudah debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>