<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191207023445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('act_document')) {
            $schema->dropTable('act_document');
        }

        if ($schema->hasTable('act_document_approbation')) {
            $schema->dropTable('act_document_approbation');
        }

        if ($schema->hasTable('act_document_topic')) {
            $schema->dropTable('act_document_topic');
        }

        if ($schema->hasTable('act_document_user')) {
            $schema->dropTable('act_document_user');
        }

        $table = $schema->createTable('act_document_topic');
        $table->addColumn('idact_document_topic', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_document_topic']);
        $table->addColumn('fk_ft_acta', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('name', 'text', [
            'notnull' => true
        ]);
        $table->addColumn('description', 'text', [
            'notnull' => true
        ]);
        $table->addColumn('state', 'integer', [
            'default' => 1
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'notnull' => false
        ]);


        $table = $schema->createTable('act_document_user');
        $table->addColumn('idact_document_user', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_document_user']);
        $table->addColumn('fk_ft_acta', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('relation', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('state', 'integer', [
            'default' => 1
        ]);
        $table->addColumn('identification', 'integer', [
            'notnull' => true
        ]);
        $table->addColumn('external', 'integer', [
            'notnull' => true
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
        if ($schema->hasTable('act_document')) {
            $schema->dropTable('act_document');
        }

        if ($schema->hasTable('act_document_approbation')) {
            $schema->dropTable('act_document_approbation');
        }

        if ($schema->hasTable('act_document_topic')) {
            $schema->dropTable('act_document_topic');
        }

        if ($schema->hasTable('act_document_user')) {
            $schema->dropTable('act_document_user');
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
