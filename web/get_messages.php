<?php
require 'vendor/autoload.php';
session_start();

use MongoDB\Client;

date_default_timezone_set('Europe/Madrid');

$client = new Client("mongodb://localhost:27017");
$collection = $client->chat->missatges;

$emissor = $_SESSION['usuari'];
$receptor = $_GET['receptor'] ?? '';

if (!$receptor) {
    echo json_encode([]);
    exit;
}

$filter = [
    '$or' => [
        ['emissor' => $emissor, 'receptor' => $receptor],
        ['emissor' => $receptor, 'receptor' => $emissor]
    ]
];

$options = ['sort' => ['timestamp' => 1]];

$result = $collection->find($filter, $options);

$missatges = [];

foreach ($result as $doc) {
    $missatges[] = [
        'emissor' => $doc['emissor'],
        'text' => $doc['text'],
        'data' => $doc['timestamp']->toDateTime()->format('d/m/Y H:i')
    ];
}

header('Content-Type: application/json');
echo json_encode($missatges);
?>

