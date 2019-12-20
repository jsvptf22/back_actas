<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191217230251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('act_question_vote')) {
            $schema->dropTable('act_question_vote');
        }

        $table = $schema->createTable('act_question_vote');
        $table->addColumn('idact_question_vote', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_question_vote']);
        $table->addColumn('fk_funcionario', 'integer', [
            'notnull' => false,
            'length' => 11
        ]);
        $table->addColumn('fk_act_question', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('action', 'integer', [
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
        if ($schema->hasTable('act_question_vote')) {
            $schema->dropTable('act_question_vote');
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
