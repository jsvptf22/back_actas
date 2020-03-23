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

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Saia\core\DatabaseConnection;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;

$Connection = DatabaseConnection::getInstance();
$Connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
$Configuration = Setup::createAnnotationMetadataConfiguration(array("entities"), true, null, null, false);
$EntityManager = EntityManager::create($Connection, $Configuration);

return new HelperSet([
    'db' => new ConnectionHelper($Connection),
    'em' => new EntityManagerHelper($EntityManager)
]);
