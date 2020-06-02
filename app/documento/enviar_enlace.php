<?php

use Saia\Actas\controllers\MeetMailInvitation;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentUser;
use Saia\controllers\JwtController;
use Saia\controllers\notificaciones\NotifierController;

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
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['documentId'] && !$_REQUEST['shedule']) {
        throw new Exception('Documento invalido', 1);
    }

    if ($_REQUEST['documentId']) {
        $FtActa = FtActa::findByDocumentId($_REQUEST['documentId']);
    } else if ($_REQUEST['shedule']) {
        $FtActa = FtActa::findByAttributes([
            'fk_agendamiento_act' => $_REQUEST['shedule']
        ]);
    }

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

    $ActaMailInvitation = new MeetMailInvitation($FtActa);
    echo "<pre>";
    var_dump($ActaMailInvitation->send($emails));
    echo "</pre>";
    exit;

    $Response->message = "Notificación enviada";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);