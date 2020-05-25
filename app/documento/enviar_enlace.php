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

    if (!$_REQUEST['documentId']) {
        throw new \Exception('Documento invalido', 1);
    }

    $emails = [];

    foreach ($_REQUEST['data'] as $item) {
        $ActDocumentUser = new ActDocumentUser();
        $ActDocumentUser->setAttributes([
            'identification' => $item['id'],
        ]);
        array_push($emails, $ActDocumentUser->getUserEmail());
    }

    $FtActa = FtActa::findByDocumentId($_REQUEST['documentId']);
    $ActaMailInvitation = new MeetMailInvitation($FtActa);
    $ActaMailInvitation->send($emails);

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