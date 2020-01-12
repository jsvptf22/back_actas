<?php

use Saia\Actas\models\ActQuestionVote;
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
    if (!isset($_REQUEST['action'])) {
        throw new Exception('Debe indicar la acción', 1);
    }

    if (!$_REQUEST['question']) {
        throw new Exception('Debe indicar la pregunta', 1);
    }

    $pk = ActQuestionVote::newRecord([
        'fk_act_question' => $_REQUEST['question'],
        'action' => $_REQUEST['action'],
    ]);

    if (!$pk) {
        throw new Exception("Error al guardar el voto", 1);
    }

    $Response->message = "Voto enviado";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
