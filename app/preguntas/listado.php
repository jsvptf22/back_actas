<?php

use Saia\Actas\models\ActQuestion;
use Saia\Actas\models\ActQuestionVote;
use Saia\controllers\JwtController;

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
    "total" => 0,
    "rows" => []
];

try {
    JwtController::check($_REQUEST['token'], $_REQUEST['key']);

    if (!$_REQUEST['id']) {
        throw new Exception('Debe indicar el documento', 1);
    }

    $questions = ActQuestion::findAllByAttributes([
        'state' => 1,
        'fk_ft_acta' => $_REQUEST['id']
    ], [], 'idact_question desc');

    foreach ($questions as $key => $ActQuestion) {
        $total = ActQuestionVote::countRecords([
            'fk_act_question' => $ActQuestion->getPK()
        ]);

        $totalReject = ActQuestionVote::countRecords([
            'fk_act_question' => $ActQuestion->getPK(),
            'action' => ActQuestionVote::ACTION_REJECT
        ]);

        $totalApprove = ActQuestionVote::countRecords([
            'fk_act_question' => $ActQuestion->getPK(),
            'action' => ActQuestionVote::ACTION_APPROVE
        ]);
        array_push($Response->rows, [
            "id" => $ActQuestion->getPK(),
            "question" => $ActQuestion->question,
            "approve" => $totalApprove . " / " . $total,
            "reject" => $totalReject . " / " . $total
        ]);
    }
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response);
