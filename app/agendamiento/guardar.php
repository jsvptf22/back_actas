<?php

use Saia\Actas\controllers\FtActaService;
use Saia\Actas\formatos\acta\FtActa;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;
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
    SessionController::goUp($_REQUEST['token'], $_REQUEST['key']);

    $userId = SessionController::getValue('idfuncionario');
    $firstRole = VfuncionarioDc::getFirstUserRole($userId);
    $defaultAssistant = (object)[
        'id' => $firstRole,
        'external' => 0
    ];
    $userList = json_decode($_REQUEST['users']);
    array_push($userList, $defaultAssistant);

    $documentData = (object)[
        'initialDate' => $_REQUEST['initialDate'],
        'subject' => $_REQUEST['subject'],
        'duracion' => $_REQUEST['duration'],
        'userList' => $userList
    ];

    $FtActa = new FtActa();
    $FtActaController = new FtActaService($FtActa);
    $FtActaController->saveDocument($documentData, $userId);
    $FtActaController->sendInvitations();

    $Response->message = "Agendamiento creado con éxito";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
