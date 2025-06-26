<?php

if(empty($_POST['from_currency']) || empty($_POST['to_currency'])) {
    echo json_encode(['error' => 'Devises d\'entrée et de sortie manquantes']);
    exit;
}

if(empty($_POST['method'])){
    echo json_encode(['error' => 'Méthode obligatoire']);
    exit;
}

require_once '../classes/xe.php';

$xe = new XE($_POST);

switch ($_POST['method']) {
    case 'getRates':
        $response = $xe->getAllRates($_POST);
        break;
    case 'getConversion':
        $response = $xe->getConversion($_POST);
        break;
    default:
        echo json_encode(['error' => 'Méthode inconnue']);
        exit;
}



if (isset($response['error'])) {
    echo json_encode(['error' => $response['error']]);
} else {
    print_r($response);
}