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

    $FtActa = !empty($_REQUEST['documentId'])
        ? FtActa::findByDocumentId($_REQUEST['documentId'])
        : FtActa::findByAttributes([
            'fk_agendamiento_act' => $_REQUEST['schedule']
        ]);

    if (!$FtActa) {
        throw new Exception("Documento invalido", 1);
    }

    $FtActaController = new FtActaService($FtActa);
    $Response->data = $FtActaController->getDocumentBuilderData();
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    echo "<pre>";
    var_dump($th);
    echo "</pre>";
    exit;
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
