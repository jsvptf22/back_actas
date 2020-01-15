<?php

use \Saia\core\DatabaseConnection;

require '../../../vendor/autoload.php';

use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Symfony\Component\Console\Helper\HelperSet;

$Connection = DataBaseConnection::getInstance();

return new HelperSet([
    'db' => new ConnectionHelper($Connection)
]);
