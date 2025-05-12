<?php
require 'vendor/autoload.php';
session_start();

use MongoDB\Client;

$data = json_decode(file_get_contents('php://input'), true);
$emissor = $data['emissor'] ?? '';
$receptor = $_SESSION['usuari'];

if ($emissor) {
    $client = new Client("mongodb://localhost:27017");
    $collection = $client->chat->missatges;

    $result = $collection->updateMany(
        [
            'emissor' => $emissor,
            'receptor' => $receptor,
            'llegit' => false
        ],
        ['$set' => ['llegit' => true]]
    );

    echo json_encode(['success' => true, 'modified' => $result->getModifiedCount()]);
} else {
    echo json_encode(['success' => false]);
}
?>