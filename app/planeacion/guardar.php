<?php

use Saia\Actas\models\ActPlanning;
use Saia\Actas\models\ActDocumentUser;

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

    if (isset($_REQUEST['users'])) {
        $users = json_decode($_REQUEST['users']);

        foreach ($users as $key => $user) {
            ActDocumentUser::newRecord([
                'state' => 1,
                'external' => $user->external ?? 1,
                'relation' => ActDocumentUser::RELATION_ASSISTANT,
                'identification' => $user->id,
                'fk_act_planning' => $ActPlanning->getPK()
            ]);
        }

        $ActPlanning->sendInvitations();
    }

    $Response->message = "Agendamiento creado";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
