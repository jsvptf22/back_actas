<?php

use Saia\Actas\formatos\acta\FtActa;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;
use Saia\core\DatabaseConnection;

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

    $QueryBuilder = DataBaseConnection::getDefaultConnection()
        ->createQueryBuilder()
        ->select('a.*')
        ->from('ft_acta', 'a')
        ->join('a', 'act_document_user', 'b', 'a.idft_acta = b.fk_ft_acta')
        ->join('b', 'vfuncionario_dc', 'c', 'b.identification = c.iddependencia_cargo')
        ->where('c.idfuncionario = :userId')
        ->andWhere('b.state = 1')
        ->setParameter('userId', SessionController::getValue('idfuncionario'))
        ->orderBy('a.fecha_final', 'asc');

    $records = FtActa::findByQueryBuilder($QueryBuilder);

    foreach ($records as $key => $FtActa) {
        $Response->data->list[] = [
            'id' => $FtActa->getPK(),
            'documentId' => $FtActa->documento_iddocumento,
            'label' => $FtActa->asunto,
            'date' => $FtActa->getDateAttribute('fecha_final'),
        ];
    }

    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
