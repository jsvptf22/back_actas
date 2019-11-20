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

use Saia\Actas\controllers\DecirHolaMundo;

$Response = (object) [
    'data' => new stdClass(),
    'message' => '',
    'success' => 0,
    'notifications' => ''
];

try {
    //JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['subject']) {
        throw new Exception('Debe indicar el asunto', 1);
    }

    $DecirHolaMundo = new DecirHolaMundo();
    $Response->message = $DecirHolaMundo->decir();
    /*$data = (object) $_REQUEST;

    if (!empty($data->documentId)) {
        $ActDocument = ActDocument::find($data->documentId);
    } else {
        $ActDocument = new ActDocument();
    }

    $ActDocument->subject = $data->subject;

    if (!$ActDocument->save()) {
        throw new \Exception("Error al guardar", 1);
    }

    $this->bindUsers(
        $request,
        $ActDocument->idact_document,
        $data
    );

    $Response->data->topics = $this->saveTopics(
        $data->topicList,
        $data->topicListDescription,
        $ActDocument->idact_document
    );
    $Response->data->document = [
        'id' => $ActDocument->idact_document,
        'identificator' => $ActDocument->identificator,
        'initialDate' => $ActDocument->created_at->format('Y-m-d H:i:s'),
        'finalDate' => $ActDocument->updated_at->format('Y-m-d H:i:s')
    ];
    $Response->success = 1;
    $Response->message = "Docuento guardado";
*/


    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
