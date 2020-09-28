<?php
$max_salida = 10;
$rootPath = $ruta = '';

while ($max_salida > 0) {
    if (is_file($ruta . 'index.php')) {
        $rootPath = $ruta;
        break;
    }

    $ruta .= '../';
    $max_salida--;
}

include_once $rootPath . 'app/vendor/autoload.php';

use Saia\Actas\controllers\FtActaService;
use Saia\Actas\formatos\acta\FtActa;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;

$Response = (object)[
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    SessionController::goUp($_REQUEST['token'], $_REQUEST['key']);

    $FtActa = FtActa::findByDocumentId($_REQUEST['documentId']);

    if (!$FtActa) {
        throw new Exception("Documento invalido", 1);
    }

    $FtActaController = new FtActaService($FtActa);
    $Response->data = $FtActaController->getDocumentBuilderData();
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
