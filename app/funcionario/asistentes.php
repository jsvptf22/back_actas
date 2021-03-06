<?php

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

    if ($_REQUEST['term']) {
        $query = $_REQUEST['term'];
        $data = DataBaseConnection::getDefaultConnection()
            ->createQueryBuilder()
            ->select(['id', 'nombre_completo as name', 'externo as external'])
            ->from('v_act_user')
            ->where('nombre_completo like :query')
            ->andWhere('estado = 1')
            ->setParameter('query', "%{$query}%")
            ->setFirstResult(0)
            ->setMaxResults(20)
            ->execute()->fetchAll();
    } else if (!empty($_REQUEST['defaultUser'])) {
        $data = DataBaseConnection::getDefaultConnection()
            ->createQueryBuilder()
            ->select(['id', 'nombre_completo as name', 'externo as external'])
            ->from('v_act_user')
            ->where('id = :identificator')
            ->andWhere('externo = :external')
            ->setParameter('identificator', $_REQUEST['id'])
            ->setParameter('query', $_REQUEST['external'])
            ->execute()->fetch();
    }

    $Response->data = $data;
    $Response->notifications = NotifierController::prepare();
    $Response->success = 1;
} catch (Throwable $th) {
    $Response->message = $th->getMessage();
}

echo json_encode($Response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
