<?php

use Saia\Actas\controllers\PublicUserPreparer;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentUser;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;
use Saia\core\DatabaseConnection;

$max_salida = 10;
$rootPath = $ruta = '';

while ($max_salida > 0) {
    if (is_file($ruta . 'index.php')) {
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
        ->andWhere('b.relation = :assistant')
        ->setParameter('assistant', ActDocumentUser::RELATION_ASSISTANT)
        ->setParameter('userId', SessionController::getValue('idfuncionario'))
        ->orderBy('a.fecha_final', 'asc');

    $records = FtActa::findByQueryBuilder($QueryBuilder);
    $items = [];

    foreach ($records as $key => $FtActa) {
        $FtActaService = $FtActa->getFtActaService();
        $internals = $FtActaService->getAssistants(ActDocumentUser::INTERNAL);
        $preparedInternals = PublicUserPreparer::getFromCollection($internals);

        $externals = $FtActaService->getAssistants(ActDocumentUser::EXTERNAL);
        $preparedExternals = PublicUserPreparer::getFromCollection($externals);

        $ActDocumentUser = $FtActaService->getRole(ActDocumentUser::RELATION_ORGANIZER);
        $preparedOrganizer = $ActDocumentUser->prepareData();

        $items[] = [
            'id' => $FtActa->getPK(),
            'documentId' => $FtActa->documento_iddocumento,
            'label' => $FtActa->asunto,
            'date' => $FtActa->getDateAttribute('fecha_final'),
            'maker' => $preparedOrganizer,
            'internalAssistants' => $preparedInternals,
            'externalAssistants' => $preparedExternals
        ];
    }

    $Response->data->list = $items;
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
