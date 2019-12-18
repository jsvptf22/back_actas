<?php
$max_salida = 10;
$rootPath = $ruta = '';

while ($max_salida > 0) {
    if (is_file($ruta . 'sw.js')) {
        $rootPath = $ruta;
        break;
    }

    $ruta .= '../';
    $max_salida--;
}

include_once $rootPath . 'app/vendor/autoload.php';

use Saia\Actas\controllers\FtActaController;
use Saia\Actas\formatos\acta\FtActa;

$Response = (object) [
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    $data = json_decode($_REQUEST['documentInformation']);

    $FtActa = FtActa::findByDocumentId($data->documentId);
    $FtActaController = new FtActaController($FtActa);
    $FtActaController->saveDocument($data);

    $Response->data = $FtActaController->getDocumentBuilderData();
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
