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
use Saia\Actas\models\ActDocumentUser;
use Saia\Actas\models\ActPlanning;

$Response = (object) [
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (isset($_REQUEST['documentId'])) {
        $FtActa = FtActa::findByDocumentId($_REQUEST['documentId']);

        if (!$FtActa) {
            throw new Exception("Documento invalido", 1);
        }

        $FtActaController = new FtActaController($FtActa);
        $Response->data = $FtActaController->getDocumentBuilderData();
    } else if (isset($_REQUEST['planning'])) {
        $ActPlanning = new ActPlanning($_REQUEST['planning']);

        if (!$ActPlanning) {
            throw new Exception("Planeacion invalida", 1);
        }

        $assistants = [];

        foreach ($ActPlanning->getUserRelations() as $ActDocumentUser) {
            array_push($assistants, $ActDocumentUser->prepareData());
        }

        $Response->data =  [
            'initialDate' => $ActPlanning->date,
            'subject' => $ActPlanning->subject,
            'userList' => $assistants,
        ];
    } else {
        throw new Exception('Debe indicar un criterio de busqueda', 1);
    }

    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
