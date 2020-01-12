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
use Saia\controllers\JwtController;
use Saia\controllers\notificaciones\NotifierController;

$Response = (object)[
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    $FtActa = isset($_REQUEST['documentId'])
        ? FtActa::findByDocumentId($_REQUEST['documentId'])
        : FtActa::findByAttributes([
            'fk_act_planning' => $_REQUEST['planning']
        ]);

    if (!$FtActa) {
        throw new Exception("Documento invalido", 1);
    }

    $FtActaController = new FtActaController($FtActa);
    $Response->data = $FtActaController->getDocumentBuilderData();
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
