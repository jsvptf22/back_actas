<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191217174010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('act_question')) {
            $schema->dropTable('act_question');
        }

        $table = $schema->createTable('act_question');
        $table->addColumn('idact_question', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_question']);
        $table->addColumn('question', 'text', [
            'notnull' => true,
        ]);
        $table->addColumn('state', 'integer', [
            'default' => 1
        ]);
        $table->addColumn('fk_funcionario', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('fk_ft_acta', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'notnull' => false
        ]);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('act_question')) {
            $schema->dropTable('act_question');
        }
    }

    public function preUp(Schema $schema): void
    {
        date_default_timezone_set("America/Bogota");

        if ($this->connection->getDatabasePlatform()->getName() == "mysql") {
            $this->platform->registerDoctrineTypeMapping('enum', 'string');
        }
    }

    public function preDown(Schema $schema): void
    {
        date_default_timezone_set("America/Bogota");

        if ($this->connection->getDatabasePlatform()->getName() == "mysql") {
            $this->platform->registerDoctrineTypeMapping('enum', 'string');
        }
    }
}
