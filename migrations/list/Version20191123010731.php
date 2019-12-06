<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191123010731 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("create or replace VIEW v_act_user AS 
        (select 
            funcionario.idfuncionario AS id,
            funcionario.login AS usuario,
            funcionario.email AS correo,
            concat(funcionario.nombres,' ',funcionario.apellidos) AS nombre_completo,
            funcionario.estado AS estado,
            0 AS externo 
        from funcionario)
        union 
        (select 
            tercero.idtercero AS id,
            '' AS usuario,
            tercero.correo AS correo,
            tercero.nombre as nombre_completo,
            tercero.estado AS estado,
            1 AS externo 
        from tercero)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('drop view v_act_user');
    }
}
