<?php
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

use Saia\Actas\formatos\acta\FtActa;
use Saia\Actas\formatos\tema\FtTema;

$Response = (object) [
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    $data = json_decode($_REQUEST['documentInformation']);

    $Formato = Formato::findByAttributes([
        'nombre' => 'acta'
    ]);

    $attributes = [
        'fecha_final' => $data->initialDate,
        'asunto' => $data->subject,
        'dependencia' => getRole(),
        'asistentes_externos' => getExternals($data->userList),
        'asistentes_internos' => getInternals($data->userList),
        'estado' => 1
    ];

    $GuardarFtController = new GuardarFtController($Formato->getPK());

    if ($data->id) {
        $documentId = $GuardarFtController->edit($attributes, $data->id);
        $Response->message = "Documento actualizado";
    } else {
        $attributes['fecha_inicial'] = date('Y-m-d H:i:s');
        $documentId = $GuardarFtController->create($attributes);
        $Response->message = "Documento creado";
    }

    $FtActa = FtActa::findByDocumentId($documentId);
    $Response->data->topics = FtTema::refreshItems(
        $FtActa,
        $data->topicList,
        $data->topicListDescription
    );

    $Response->data->document = [
        'id' => $documentId,
        'identificator' => $FtActa->Documento->numero,
        'initialDate' => $FtActa->fecha_inicial,
        'finalDate' => $FtActa->fecha_final,
    ];
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    echo '<pre>';
    var_dump($th);
    echo '</pre>';
    exit;
    $Response->message = $th->getMessage();
}

echo json_encode($Response);

/**
 * obtiene el primer rol del funcionario de la sesion
 *
 * @return integer
 * @author jhon sebastian valencia <jhon.valencia@cerok.com>
 * @date 2019-11-26
 */
function getRole()
{
    $VfuncionarioDc = VfuncionarioDc::findByAttributes([
        'estado' => 1,
        'estado_dc' => 1,
        'tipo_cargo' => 1,
        'idfuncionario' => SessionController::getValue('idfuncionario')
    ]);

    return $VfuncionarioDc->iddependencia_cargo;
}

/**
 * obtiene los usuarios externos
 *
 * @param array $userList
 * @return array
 * @author jhon sebastian valencia <jhon.valencia@cerok.com>
 * @date 2019-11-26
 */
function getExternals($userList)
{
    $users = [];

    foreach ($userList as $key => $user) {
        if ((int) $user->external == 1) {
            array_push($users, $user->id);
        }
    }

    return $users;
}

/**
 * obtiene los usuarios internos
 *
 * @param array $userList
 * @return array
 * @author jhon sebastian valencia <jhon.valencia@cerok.com>
 * @date 2019-11-26
 */
function getInternals($userList)
{
    $users = [];

    foreach ($userList as $key => $user) {
        if ((int) $user->external == 0) {
            array_push($users, $user->id);
        }
    }

    return $users;
}
