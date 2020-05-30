<?php

use Saia\Actas\controllers\FtAgendamientoActaController;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;

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

    $data = (object)[
        'duration' => $_REQUEST['duration'],
        'subject' => $_REQUEST['subject'],
        'initialDate' => $_REQUEST['initialDate'],
        'users' => $_REQUEST['users'],
    ];
    $FtAgendamientoActaController = new FtAgendamientoActaController($data);
    $FtAgendamientoActaController->save();

    $Response->message = "Agendamiento creado con éxito";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
