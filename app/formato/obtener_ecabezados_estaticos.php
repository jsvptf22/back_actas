<?php

use Saia\controllers\functions\CoreFunctions;
use Saia\controllers\functions\Header;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;
use Saia\controllers\Utilities;
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

$Response = (object)[
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    SessionController::goUp($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['format']) {
        throw new \Exception('Debe indicar el formato', 1);
    }

    $Formato = Formato::findByAttributes([
        'nombre' => $_REQUEST['format']
    ]);

    $headerBase = $Formato->getHeader()->contenido;
    $footerBase = $Formato->getFooter()->contenido;

    $Response->data->header = replaceFunction($headerBase);
    $Response->data->footer = replaceFunction($footerBase);
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);

function replaceFunction($baseContent)
{
    $Formato = Formato::findByAttributes(['nombre' => 'acta']);
    $functions = Header::getFunctionsFromString($baseContent);
    $functions = str_replace(['{*', '*}'], '', $functions);
    $values = CoreFunctions::getVariableValue($functions);
    $values['nombre_formato'] = CoreFunctions::nombre_formato(null, $Formato->getPK());

    foreach ($values as $key => $value) {
        $baseContent = str_replace(
            "{*{$key}*}",
            $value,
            $baseContent
        );
    }

    return $baseContent;
}
