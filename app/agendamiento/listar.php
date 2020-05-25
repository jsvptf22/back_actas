<?php

use Saia\Actas\formatos\agendamiento_acta\FtAgendamientoActa;
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
        ->from('ft_agendamiento_acta', 'a')
        ->join('a', 'act_document_user', 'b', 'a.idft_agendamiento_acta = b.fk_agendamiento_act')
        ->join('b', 'vfuncionario_dc', 'c', 'b.identification = c.iddependencia_cargo')
        ->where('c.idfuncionario = :userId')
        ->andWhere('b.state = 1 and a.state =1')
        ->setParameter('userId', SessionController::getValue('idfuncionario'))
        ->orderBy('a.date', 'asc');

    $records = FtAgendamientoActa::findByQueryBuilder($QueryBuilder);

    foreach ($records as $key => $FtAgendamientoActa) {
        $Response->data->list[] = [
            'id' => $FtAgendamientoActa->getPK(),
            'label' => $FtAgendamientoActa->subject,
            'date' => $FtAgendamientoActa->getDateAttribute('date'),
        ];
    }

    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
