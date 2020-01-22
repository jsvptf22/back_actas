<?php

use Saia\controllers\JwtController;
use Saia\controllers\notificaciones\NotifierController;
use Saia\models\formatos\CamposFormato;
use Saia\models\formatos\Formato;

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

    if (!$_REQUEST['field']) {
        throw new \Exception('Debe indicar el nombre del campo', 1);
    }

    if (!$_REQUEST['formatName']) {
        throw new \Exception('Debe indicar el nombre del formato', 1);
    }

    $Formato = Formato::findByAttributes([
        'nombre' => $_REQUEST['formatName'],
    ]);

    if (!$Formato) {
        throw new Exception("Formato indefinido", 1);
    }

    $CamposFormato = CamposFormato::findByAttributes([
        'nombre' => $_REQUEST['field'],
        'formato_idformato' => $Formato->getPK()
    ]);

    if (!$CamposFormato) {
        throw new Exception("Campo indefinido", 1);
    }

    $Response->data = $CamposFormato->getAttributes();
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
