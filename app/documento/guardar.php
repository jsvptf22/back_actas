<?php

use Saia\Actas\controllers\FtActaService;
use Saia\Actas\formatos\acta\FtActa;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;
use Saia\core\DatabaseConnection;
use Saia\models\vistas\VfuncionarioDc;

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

$Response = (object)[
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    $Connection = DatabaseConnection::getDefaultConnection();
    $Connection->beginTransaction();

    SessionController::goUp($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['documentInformation']) {
        throw new Exception('Debe indicar la información del documento', 1);
    }

    $userId = SessionController::getValue('idfuncionario');
    $VfuncionarioDc = VfuncionarioDc::getActiveRoles($userId)[0];

    $data = json_decode($_REQUEST['documentInformation']);
    $FtActa = $data->documentId ?
        FtActa::findByDocumentId($data->documentId) : new FtActa();
    $FtActaController = new FtActaService($FtActa);
    $FtActaController->saveDocument($data, $VfuncionarioDc);

    $Response->data = $FtActaController->getDocumentBuilderData();
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
    $Connection->commit();
} catch (Throwable $th) {
    $Connection->rollBack();
    $Response->message = $th->getMessage();
}

echo json_encode($Response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);