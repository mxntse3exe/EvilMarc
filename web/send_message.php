<?php
require 'vendor/autoload.php';
session_start();

use MongoDB\Client;

date_default_timezone_set('Europe/Madrid');

$data = json_decode(file_get_contents('php://input'), true);

$emissor = $_SESSION['usuari'];
$receptor = $data['receptor'] ?? '';
$text = $data['text'] ?? '';

if ($receptor && $text) {
    $client = new Client("mongodb://localhost:27017");
    $collection = $client->chat->missatges;

    $collection->insertOne([
        'emissor' => $emissor,
        'receptor' => $receptor,
        'text' => $text,
        'llegit' => false, // Nuevo mensaje no leído
        'timestamp' => new MongoDB\BSON\UTCDateTime((new DateTime('+2 hours'))->getTimestamp() * 1000)
    ]);

    echo json_encode(['status' => 'ok']);
} else {
    echo json_encode(['status' => 'error']);
}
?>