<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191210005914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('act_planning');
        $table->addColumn('idact_planning', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_planning']);
        $table->addColumn('date', 'datetime', [
            'notnull' => true
        ]);
        $table->addColumn('subject', 'text', [
            'notnull' => true,
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

        $table = $schema->getTable('act_document_user');
        $table->addColumn('fk_act_planning', 'integer', [
            'notnull' => false,
            'length' => 11
        ]);
        $table->changeColumn('fk_ft_acta', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('act_planning')) {
            $schema->dropTable('act_planning');
        }

        $table = $schema->getTable('act_document_user');

        if ($table->hasColumn('fk_act_planning')) {
            $table->dropColumn('fk_act_planning');
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
