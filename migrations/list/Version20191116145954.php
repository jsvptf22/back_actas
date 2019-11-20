<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191116145954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->connection->insert('modulo', [
            'pertenece_nucleo' => '1',
            'nombre' => 'agrupador_actas',
            'tipo' => '0',
            'imagen' => 'fa fa-file',
            'etiqueta' => 'Actas',
            'enlace' => '',
            'cod_padre' => '0',
            'orden' => '5'
        ]);

        $this->connection->insert('modulo', [
            'pertenece_nucleo' => '1',
            'nombre' => 'dashboard_actas',
            'tipo' => '1',
            'imagen' => 'fa fa-dashboard',
            'etiqueta' => 'Indicadores',
            'enlace' => 'views/modules/actas/views/dashboard/dashboard.php',
            'cod_padre' => $this->connection->lastInsertId(),
            'orden' => '1'
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->connection->delete('modulo', [
            'nombre' => 'agrupador_actas'
        ]);

        $this->connection->delete('modulo', [
            'nombre' => 'dashboard_actas'
        ]);
    }
}
