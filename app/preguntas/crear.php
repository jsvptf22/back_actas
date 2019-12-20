<?php

use Saia\Actas\models\ActQuestion;

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

    if (!$_REQUEST['question']) {
        throw new Exception('Debe indicar la pregunta', 1);
    }

    $pk = ActQuestion::newRecord([
        'question' => $_REQUEST['question'],
        'fk_funcionario' => SessionController::getValue('idfuncionario'),
        'fk_ft_acta' => $_REQUEST['id']
    ]);

    if (!$pk) {
        throw new Exception("Error al publicar", 1);
    }

    $Response->message = "Pregunta publicada";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
