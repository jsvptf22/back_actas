<?php

use Saia\Actas\controllers\MeetMailInvitation;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentUser;
use Saia\controllers\JwtController;
use Saia\controllers\notificaciones\NotifierController;

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

$Response = (object)[
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['documentId']) {
        throw new Exception('Documento invalido', 1);
    }

    $FtActa = FtActa::findByDocumentId($_REQUEST['documentId']);

    $emails = [];
    if ($_REQUEST['data']) {
        foreach ($_REQUEST['data'] as $item) {
            $ActDocumentUser = new ActDocumentUser();
            $ActDocumentUser->setAttributes([
                'identification' => $item['id'],
            ]);
            array_push($emails, $ActDocumentUser->getUserEmail());
        }
    }

    $ActaMailInvitation = new MeetMailInvitation($FtActa->getFtActaService());
    $ActaMailInvitation->send($emails);

    $Response->message = "Notificación enviada";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);