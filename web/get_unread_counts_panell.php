<?php
session_start();
require 'vendor/autoload.php';
use MongoDB\Client;

$mongoClient = new Client("mongodb://localhost:27017");
$client = $mongoClient;

// Comptar missatges no llegits
$mongoFilter = [
    'receptor' => $_SESSION['usuari'],
    'llegit' => false
];
$no_llegits = $client->chat->missatges->countDocuments($mongoFilter);

header('Content-Type: application/json');
echo json_encode(['total' => $no_llegits]);
?>