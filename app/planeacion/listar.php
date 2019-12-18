<?php

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

    $QueryBuilder = Model::getQueryBuilder()
        ->select('a.*')
        ->from('act_planning', 'a')
        ->join('a', 'act_document_user', 'b', 'a.idact_planning = b.fk_act_planning')
        ->where('b.identification = :userId')
        ->andWhere('b.state = 1 and a.state =1')
        ->setParameter('userId', SessionController::getValue('idfuncionario'))
        ->orderBy('a.date', 'asc');

    $records = ActPlanning::findByQueryBuilder($QueryBuilder);

    foreach ($records as $key => $ActPlanning) {
        $Response->data->list[] = [
            'id' => $ActPlanning->getPK(),
            'label' => $ActPlanning->subject,
            'date' => $ActPlanning->getDateAttribute('date'),
        ];
    }

    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
