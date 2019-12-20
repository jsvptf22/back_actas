<?php

use Saia\Actas\controllers\FtActaController;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActPlanning;

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

$Response = (object) [
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['subject']) {
        throw new Exception('Debe indicar el asunto', 1);
    }

    if (!$_REQUEST['initialDate']) {
        throw new Exception('Debe indicar la fecha', 1);
    }

    $ActPlanning = new ActPlanning();
    $ActPlanning->setAttributes([
        'subject' => $_REQUEST['subject'],
        'date' => $_REQUEST['initialDate'],
        'state' => 1,
    ]);

    if (!$ActPlanning->save()) {
        throw new Exception("Error al agendar", 1);
    }

    $defaultAssistant = (object) [
        'id' => SessionController::getValue('idfuncionario'),
        'external' => 0
    ];
    $userList = json_decode($_REQUEST['users']);
    array_push($userList, $defaultAssistant);

    $data = (object) [
        'planning' => $ActPlanning->getPK(),
        'initialDate' => $ActPlanning->date,
        'subject' => $ActPlanning->subject,
        'userList' => $userList
    ];

    $FtActa = new FtActa();
    $FtActaController = new FtActaController($FtActa);
    $FtActaController->saveDocument($data);
    $FtActaController->sendInvitations();

    $Response->message = "Agendamiento creado";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
