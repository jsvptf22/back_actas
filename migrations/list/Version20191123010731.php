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
        $this->addSql("CREATE or replace VIEW `v_act_user` AS
        (
            SELECT 
                `vfuncionario_dc`.`iddependencia_cargo` AS `id`,
                `vfuncionario_dc`.`login` AS `usuario`,
                `vfuncionario_dc`.`email` AS `correo`,
                UPPER(CONCAT(
                    `vfuncionario_dc`.`nombres`,
                    ' ',
                    `vfuncionario_dc`.`apellidos`,
                    ' - ',
                    `vfuncionario_dc`.`cargo`
                )) AS `nombre_completo`,
                `vfuncionario_dc`.`estado_dc` AS `estado`,
                0 AS `externo`
            FROM
                `vfuncionario_dc`
            WHERE
                `vfuncionario_dc`.`estado_dc` = 1
        ) UNION (SELECT 
            `tercero`.`idtercero` AS `id`,
            '' AS `usuario`,
            `tercero`.`correo` AS `correo`,
            UPPER(`tercero`.`nombre`) AS `nombre_completo`,
            `tercero`.`estado` AS `estado`,
            1 AS `externo`
        FROM
            `tercero`)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('drop view v_act_user');
    }
}
