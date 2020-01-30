<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200130190150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->connection->delete('pantalla_grafico', [
            'nombre' => 'actas'
        ]);

        $idpantalla_grafico = $this->connection->insert('pantalla_grafico', [
            'nombre' => 'actas'
        ]);



        $this->connection->delete('grafico', [
            "nombre" => "estado_tareas_acta",
        ]);

        $this->connection->insert('grafico', [
            "fk_busqueda_componente" => null,
            "fk_pantalla_grafico" => $idpantalla_grafico,
            "nombre" => "estado_tareas_acta",
            "tipo" => "2",
            "configuracion" => NULL,
            "estado" => 1,
            "query" => 'SELECT b.valor,COUNT(*) FROM tarea a JOIN tarea_estado b ON a.idtarea = b.fk_tarea JOIN tarea_funcionario c ON a.idtarea = c.fk_tarea JOIN documento_tarea d ON a.idtarea = d.fk_tarea JOIN documento e ON d.fk_documento = e.iddocumento WHERE b.estado = 1 AND c.estado = 1 AND c.fk_funcionario = {*logged_userId*} AND c.tipo = 1 AND e.plantilla = \'acta\' GROUP BY b.valor',
            "modelo" => 'Saia\\models\\tarea\\TareaEstado',
            "columna" => 'valor',
            "titulo_x" => 'Estados',
            "titulo_y" => 'Cantidad de tareas',
            "busqueda" => null,
            "librerias" => 'app/documento/librerias.php',
            "titulo" => 'Estados de las tareas',
        ]);

        $this->connection->delete('modulo', [
            "nombre" => "graficos_actas",
        ]);

        $this->connection->insert('modulo', [
            "pertenece_nucleo" => 1,
            "nombre" => 'graficos_actas',
            "tipo" => '1',
            "imagen" => 'fa fa-bar-chart',
            "etiqueta" => 'Gráficos',
            "enlace" => 'views/graficos/dashboard.php?screen=' . $idpantalla_grafico,
            "cod_padre" => '2149',
            "orden" => '2',
            "color" => NULL
        ]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
