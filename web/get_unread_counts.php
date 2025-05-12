<?php
require 'vendor/autoload.php';
session_start();

use MongoDB\Client;

$client = new Client("mongodb://localhost:27017");
$collection = $client->chat->missatges;

$receptor = $_SESSION['usuari'];

$pipeline = [
    ['$match' => [
        'receptor' => $receptor,
        'llegit' => false
    ]],
    ['$group' => [
        '_id' => '$emissor',
        'count' => ['$sum' => 1]
    ]]
];

$result = $collection->aggregate($pipeline);

$counts = [];
foreach ($result as $doc) {
    $counts[$doc['_id']] = $doc['count'];
}

header('Content-Type: application/json');
echo json_encode($counts);
?>