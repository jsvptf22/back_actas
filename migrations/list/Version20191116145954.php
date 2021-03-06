<?php

declare(strict_types=1);

namespace Saia\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Saia\controllers\generator\component\Date;
use Saia\controllers\generator\component\ExternalUser;
use Saia\controllers\generator\component\Hidden;
use Saia\controllers\generator\component\Method;
use Saia\controllers\generator\component\Textarea;
use Saia\controllers\generator\component\UserAutocomplete;
use Saia\models\formatos\CamposFormato;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191116145954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'instalacion modulo actas';
    }

    public function up(Schema $schema): void
    {
        $this->connection->delete('modulo', [
            'nombre' => 'agrupador_actas'
        ]);

        $this->connection->delete('modulo', [
            'nombre' => 'dashboard_actas'
        ]);

        $this->connection->insert('modulo', [
            'pertenece_nucleo' => '1',
            'nombre' => 'agrupador_actas',
            'tipo' => '0',
            'imagen' => 'fa fa-file',
            'etiqueta' => 'Actas',
            'enlace' => '',
            'cod_padre' => '0',
            'orden' => '5',
            'color' => 'bg-complete'
        ]);

        $grouper = $this->connection->lastInsertId();

        $this->connection->insert('modulo', [
            'pertenece_nucleo' => '1',
            'nombre' => 'dashboard_actas',
            'tipo' => '1',
            'imagen' => 'fa fa-calendar',
            'etiqueta' => 'Agenda',
            'enlace' => 'views/modules/actas/dist/schedule/index.html',
            'cod_padre' => $grouper,
            'orden' => '1'
        ]);

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


        $format = $this->connection->fetchAll("select * from formato where nombre = 'acta'");

        if ($format[0]['idformato']) {
            $formatId = $format[0]['idformato'];

            $this->connection->delete('formato', [
                'idformato' => $formatId
            ]);

            $this->connection->delete('campos_formato', [
                'formato_idformato' => $formatId
            ]);
        }

        $this->connection->delete('categoria_formato', [
            'nombre' => 'Actas'
        ]);

        $this->connection->insert('categoria_formato', [
            'nombre' => 'Actas',
            'cod_padre' => 0,
            'estado' => 1,
            'descripcion' => '',
            'fecha' => date('Y-m-d H:i:s'),
        ]);

        $category = $this->connection->lastInsertId();

        $body = <<<HTML
<div class="row">
    <div class="col-12">
        <table class="table table-bordered">
            <tbody>
                <tr>
                    <td class="bold">Acta N&deg;</td>
                    <td>{*formato_numero*}</td>
                    <td
                        rowspan="4"
                        class="text-center align-middle"
                    >
                        {*qrCodeHtml*}
                    </td>
                </tr>
                <tr>
                    <td class="bold">Tema / Asunto</td>
                    <td>{*asunto*}</td>
                </tr>
                <tr>
                    <td class="bold">Inicio</td>
                    <td>{*fecha_inicial*}</td>
                </tr>
                <tr>
                    <td class="bold">Fin</td>
                    <td>{*fecha_final*}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row">
<div class="col-12">
<table class="table table-bordered">
	<tbody>
		<tr>
			<td class="text-center">Participantes</td>
		</tr>
		<tr>
			<td><span class="bold">Asistentes:</span> {*listInternalAssistants*}</td>
		</tr>
		<tr>
			<td><span class="bold">Invitados:</span> {*listExternalAssistants*}</td>
		</tr>
	</tbody>
</table>
</div>
</div>

<div class="row">
<div class="col-12">
<table class="table table-bordered">
	<tbody>
		<tr>
			<td class="bold text-center">Puntos a Tratar / Orden del d&iacute;a</td>
		</tr>
		<tr>
			<td>
				{*listTopics*}
			</td>
		</tr>
	</tbody>
</table>
</div>
</div>

<div class="row">
<div class="col-12">
<table class="table table-bordered">
	<tbody>
		<tr>
			<td class="bold text-center">Puntos Tratados / Desarrollo</td>
		</tr>
		<tr>
			<td>{*listTopicDescriptions*}</td>
		</tr>
	</tbody>
</table>
</div>
</div>

<div class="row">
<div class="col-12">
{*listQuestions*}
</div>
</div>

<div class="row">
<div class="col-12">
{*listTasks*}
</div>
</div>

<div class="row">
<div class="col-12">
<table class="table table-bordered">
	<tbody>
		<tr>
			<td>{*mostrar_estado_proceso*}</td>
		</tr>
	</tbody>
</table>
</div>
</div>
HTML;
        $acta = [
            "nombre" => "acta",
            "etiqueta" => "Acta",
            "cod_padre" => 0,
            "contador_idcontador" => 4,
            "nombre_tabla" => "ft_acta",
            "ruta_mostrar" => "app/modules/back_actas/formatos/acta/mostrar.php",
            "ruta_editar" => "views/modules/actas/dist/documentBuilder/index.html",
            "ruta_adicionar" => "views/modules/actas/dist/documentBuilder/index.html",
            "encabezado" => "1",
            "cuerpo" => $body,
            "pie_pagina" => "4",
            "margenes" => "25,25,25,25",
            "orientacion" => null,
            "papel" => "Letter",
            "funcionario_idfuncionario" => 1,
            "detalle" => "0",
            "tipo_edicion" => 0,
            "item" => "0",
            "font_size" => "11",
            "banderas" => "asunto_padre",
            "mostrar_pdf" => 0,
            "orden" => null,
            "fk_categoria_formato" => $category,
            "pertenece_nucleo" => 1,
            "descripcion_formato" => "acta",
            "version" => 1,
            "publicar" => 1,
            "module" => "actas",
            "generador_pdf" => "Mpdf"
        ];

        $this->connection->insert('formato', $acta);
        $actaId = $this->connection->lastInsertId();

        $this->connection->delete('modulo', [
            'nombre' => 'crear_acta'
        ]);

        $row = $this->connection->fetchAll("select idmodulo from modulo where nombre = 'modulo_formatos'");
        $parentModuleId = $row[0]['idmodulo'];

        $this->connection->insert('modulo', [
            'pertenece_nucleo' => 1,
            'nombre' => 'crear_acta',
            'tipo' => '2',
            'imagen' => null,
            'etiqueta' => 'Acta',
            'enlace' => "views/modules/actas/views/document/index.php",
            'cod_padre' => $parentModuleId,
            'orden' => 1
        ]);

        $actafields = [
            [
                "formato_idformato" => $actaId,
                "nombre" => "asistentes_externos",
                "etiqueta" => "Asistentes externos",
                "tipo_dato" => Types::STRING,
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => null,
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => "",
                "predeterminado" => null,
                "banderas" => "",
                "etiqueta_html" => ExternalUser::getIdentification(),
                "orden" => 6,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => "{\"tipo_seleccion\":\"multiple\",\"tipo\":true,\"nombre\":true,\"tipo_identificacion\":true,\"identificacion\":true,\"ciudad\":false,\"titulo\":false,\"direccion\":true,\"telefono\":true,\"correo\":false,\"sede\":false,\"cargo\":true,\"empresa\":true}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "asistentes_internos",
                "etiqueta" => "Asistentes internos",
                "tipo_dato" => Types::STRING,
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => "",
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => UserAutocomplete::getIdentification(),
                "orden" => 7,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "asunto",
                "etiqueta" => "asunto",
                "tipo_dato" => Types::STRING,
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => "",
                'acciones' => sprintf("%s,%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT, CamposFormato::ACTION_DESCRIPTION),
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => Textarea::getIdentification(),
                "orden" => 2,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "campo texto con formato",
                "longitud_vis" => null,
                "opciones" => "{\"avanzado\":false}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "dependencia",
                "etiqueta" => "DEPENDENCIA DEL CREADOR DEL DOCUMENTO",
                "tipo_dato" => Types::INTEGER,
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => "",
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => CamposFormato::FLAG_INDEX,
                "etiqueta_html" => Method::getIdentification(),
                "orden" => 1,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "documento_iddocumento",
                "etiqueta" => "DOCUMENTO ASOCIADO",
                "tipo_dato" => Types::INTEGER,
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => CamposFormato::ACTION_EDIT,
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => CamposFormato::FLAG_INDEX,
                "etiqueta_html" => Hidden::getIdentification(),
                "orden" => 0,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "encabezado",
                "etiqueta" => "ENCABEZADO",
                "tipo_dato" => Types::INTEGER,
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => null,
                "predeterminado" => "1",
                "banderas" => null,
                "etiqueta_html" => Hidden::getIdentification(),
                "orden" => 0,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "estado",
                "etiqueta" => "Estado",
                "tipo_dato" => Types::STRING,
                "longitud" => "255",
                "obligatoriedad" => 0,
                "valor" => "",
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => Hidden::getIdentification(),
                "orden" => 5,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "Campo hidden",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "fecha_final",
                "etiqueta" => "Fecha final",
                "tipo_dato" => Types::DATETIME_MUTABLE,
                "longitud" => null,
                "obligatoriedad" => null,
                "valor" => "",
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => null,
                "etiqueta_html" => Date::getIdentification(),
                "orden" => 4,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => "{\"hoy\":true,\"tipo\":\"datetime\"}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "fecha_inicial",
                "etiqueta" => "Fecha inicial",
                "tipo_dato" => Types::DATETIME_MUTABLE,
                "longitud" => null,
                "obligatoriedad" => 1,
                "valor" => "",
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => null,
                "etiqueta_html" => Date::getIdentification(),
                "orden" => 3,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => "{\"hoy\":true,\"tipo\":\"datetime\"}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "firma",
                "etiqueta" => "FIRMAS DIGITALES",
                "tipo_dato" => Types::INTEGER,
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => null,
                "predeterminado" => "1",
                "banderas" => null,
                "etiqueta_html" => Hidden::getIdentification(),
                "orden" => 0,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "idft_acta",
                "etiqueta" => "acta",
                "tipo_dato" => Types::INTEGER,
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                'acciones' => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => sprintf("%s,%s", CamposFormato::FLAG_AUTOINCREMENT, CamposFormato::FLAG_PRIMARYKEY),
                "etiqueta_html" => Hidden::getIdentification(),
                "orden" => 0,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $actaId,
                "nombre" => "duracion",
                "etiqueta" => "Duración",
                "tipo_dato" => Types::INTEGER,
                "longitud" => "255",
                "obligatoriedad" => 0,
                "valor" => "",
                "acciones" => sprintf("%s,%s", CamposFormato::ACTION_ADD, CamposFormato::ACTION_EDIT),
                "ayuda" => "",
                "predeterminado" => null,
                "banderas" => "",
                "etiqueta_html" => Hidden::getIdentification(),
                "orden" => 9,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "Campo hidden",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ]
        ];

        foreach ($actafields as $field) {
            $this->connection->insert('campos_formato', $field);
        }

        /*$this->connection->delete('pantalla_grafico', [
            'nombre' => 'actas'
        ]);

        $this->connection->insert('pantalla_grafico', [
            'nombre' => 'actas'
        ]);

        $idpantalla_grafico = $this->connection->lastInsertId();

        $this->connection->delete('grafico', [
            "nombre" => "estado_tareas_acta",
        ]);

        $this->connection->insert('grafico', [
            "fk_busqueda_componente" => null,
            "fk_pantalla_grafico" => $idpantalla_grafico,
            "nombre" => "estado_tareas_acta",
            "tipo" => "2",
            "configuracion" => null,
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
            "cod_padre" => $grouper,
            "orden" => '2',
            "color" => null
        ]);
*/
        if ($schema->hasTable('act_document_topic')) {
            $schema->dropTable('act_document_topic');
        }

        $table = $schema->createTable('act_document_topic');
        $table->addColumn('idact_document_topic', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_document_topic']);
        $table->addColumn('name', 'text', [
            'notnull' => true,
        ]);
        $table->addColumn('description', 'text', [
            'notnull' => true,
        ]);
        $table->addColumn('fk_ft_acta', 'integer', [
            'notnull' => true,
            'length' => 11
        ]);
        $table->addColumn('state', 'integer', [
            'notnull' => true,
            'length' => 11,
            'default' => 1
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true,
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'notnull' => false,
        ]);


        if ($schema->hasTable('act_document_user')) {
            $schema->dropTable('act_document_user');
        }

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
            'length' => 11,
        ]);
        $table->addColumn('state', 'integer', [
            'notnull' => true,
            'length' => 11,
            'default' => 1
        ]);
        $table->addColumn('identification', 'integer', [
            'notnull' => true,
            'length' => 11,
        ]);
        $table->addColumn('external', 'integer', [
            'notnull' => true,
            'length' => 11,
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true,
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'notnull' => false,
        ]);

        if ($schema->hasTable('act_question')) {
            $schema->dropTable('act_question');
        }

        $table = $schema->createTable('act_question');
        $table->addColumn('idact_question', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_question']);
        $table->addColumn('label', 'text', [
            'notnull' => true,
        ]);
        $table->addColumn('state', 'integer', [
            'notnull' => true,
            'length' => 11,
            'default' => 1
        ]);
        $table->addColumn('fk_funcionario', 'integer', [
            'notnull' => false,
            'length' => 11
        ]);
        $table->addColumn('fk_ft_acta', 'integer', [
            'notnull' => false,
            'length' => 11
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true,
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'notnull' => false,
        ]);

        if ($schema->hasTable('act_question_option')) {
            $schema->dropTable('act_question_option');
        }

        $table = $schema->createTable('act_question_option');
        $table->addColumn('idact_question_option', 'integer', [
            'autoincrement' => true,
            'length' => 11
        ]);
        $table->setPrimaryKey(['idact_question_option']);
        $table->addColumn('label', 'text', [
            'notnull' => true,
        ]);
        $table->addColumn('fk_act_question', 'integer', [
            'notnull' => true,
            'length' => 11,
        ]);
        $table->addColumn('votes', 'integer', [
            'notnull' => true,
            'length' => 11,
        ]);
        $table->addColumn('state', 'integer', [
            'notnull' => true,
            'length' => 11,
            'default' => 1
        ]);
        $table->addColumn('created_at', 'datetime', [
            'notnull' => true,
        ]);
        $table->addColumn('updated_at', 'datetime', [
            'notnull' => false,
        ]);

        $this->connection->delete('new_actions', [
            'class_name' => 'Saia\Actas\controllers\NewScheduleAction',
        ]);

        $this->connection->insert('new_actions', [
            'class_name' => 'Saia\Actas\controllers\NewScheduleAction',
            'estado' => 1
        ]);
    }

    public function down(Schema $schema): void
    {
    }
}
