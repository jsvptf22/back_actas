<?php

return [
    'name' => 'SAIA Migrations',
    'migrations_namespace' => 'Saia\Migrations\Actas',
    'table_name' => 'migrations_actas',
    'column_name' => 'version',
    'column_length' => 14,
    'executed_at_column_name' => 'executed_at',
    'migrations_directory' => 'list',
    'all_or_nothing' => true,
    'check_database_platform' => true,
];
