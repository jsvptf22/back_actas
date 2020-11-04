<?php

use Saia\Actas\controllers\FtActaService;
use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\models\ActDocumentUser;
use Saia\controllers\notificaciones\NotifierController;
use Saia\controllers\SessionController;
use Saia\core\DatabaseConnection;
use Saia\models\vistas\VfuncionarioDc;

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
    $Connection = DatabaseConnection::getDefaultConnection();
    $Connection->beginTransaction();

    SessionController::goUp($_REQUEST['token'], $_REQUEST['key']);
    $userId = SessionController::getValue('idfuncionario');
    $VfuncionarioDc = VfuncionarioDc::getActiveRoles($userId)[0];

    $defaultAssistant = (object)[
        'id' => $VfuncionarioDc->iddependencia_cargo,
        'external' => 0
    ];
    $userList = json_decode($_REQUEST['users']);
    array_push($userList, $defaultAssistant);

    $documentData = (object)[
        'initialDate' => $_REQUEST['initialDate'],
        'subject' => $_REQUEST['subject'],
        'duracion' => $_REQUEST['duration'],
        'userList' => $userList,
        'roles' => (object)[
            'organizer' => (object)[
                'id' => $VfuncionarioDc->iddependencia_cargo,
                'external' => ActDocumentUser::INTERNAL
            ]
        ]
    ];

    $FtActa = new FtActa();
    $FtActaService = new FtActaService($FtActa);
    $FtActaService->saveDocument($documentData, $VfuncionarioDc);
    $FtActaService->sendInvitations();
    $FtActaService->createTaskEvents();

    $Response->message = "Agendamiento creado con éxito";
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
    $Connection->commit();
} catch (Throwable $th) {
    $Connection->rollBack();
    $Response->message = $th->getMessage();
}

echo json_encode($Response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
