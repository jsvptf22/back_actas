<?php

use Saia\models\formatos\Formato;
use Saia\controllers\JwtController;
use Saia\models\formatos\FuncionNucleo;
use Saia\controllers\UtilitiesController;
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

$Response = (object) [
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['format']) {
        throw new \Exception('Debe indicar el formato', 1);
    }

    $Formato = Formato::findByAttributes([
        'nombre' => $_REQUEST['format']
    ]);

    $headerBase = $Formato->getHeader()->contenido;
    $footerBase = $Formato->getFooter()->contenido;

    $Response->data->header = replaceFunction($headerBase, $Formato);
    $Response->data->footer = replaceFunction($footerBase, $Formato);
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);

function replaceFunction($content, $Formato)
{
    $systemFuntions = FuncionNucleo::findColumn('nombre');
    $functions = UtilitiesController::getFunctionsFromString($content);

    foreach ($functions as $representation) {
        $method = str_replace(['{*', '*}'], '', $representation);

        if (in_array($method, $systemFuntions)) {
            $content = str_replace(
                $representation,
                call_user_func(
                    [UtilitiesController::class, $method],
                    null,
                    $Formato->getPK()
                ),
                $content
            );
        }
    }

    return $content;
}
