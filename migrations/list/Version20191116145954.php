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

        $format = $this->connection->fetchAll("select * from formato where nombre = 'agendamiento_acta'");

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
    <tr>
        <td class="bold">Acta N°</td>
        <td>
            {*formato_numero*}
        </td>
    </tr>
    <tr>
        <td class="bold">Tema / Asunto</td>
        <td colspan="3">
            {*asunto*}
        </td>
    </tr>
    <tr>
        <td class="bold">Inicio</td>
        <td>
            {*fecha_inicial*}
        </td>
    </tr>
    <tr>
        <td class="bold">Fin</td>
        <td>{*fecha_final*}</td>
    </tr>
</table>
</div>
</div>
<div class="row">
<div class="col-12">
<table class="table table-bordered">
    <tr>
        <td class="text-center">
            Participantes
        </td>
    </tr>
    <tr>
        <td>
            <span class="bold">Asistentes:</span>
            {*listInternalAssistants*}
        </td>
    </tr>
    <tr>
        <td>
            <span class="bold">Invitados:</span>
            {*listExternalAssistants*}        
        </td>
    </tr>
</table>
</div>
</div>
<div class="row">
<div class="col-12">
<table class="table table-bordered">
    <tr>
        <td class="text-center bold">
            Puntos a Tratar / Orden del día
        </td>
    </tr>
    <tr>
        <td>
            <ul>
                <li
                    v-for="topic of documentInformation.topicList"
                    v-bind:key="topic.id"
                >
                {*listTopics*}
                </li>
            </ul>
        </td>
    </tr>
</table>
</div>
</div>
<div class="row">
<div class="col-12">
<table class="table table-bordered">
    <tr>
        <td class="text-center bold">
            Puntos Tratados / Desarrollo
        </td>
    </tr>
    <tr>
        <td>
            {*listTopicDescriptions*}
        </td>
    </tr>
</table>
</div>
</div>
<div class="row">
<div class="col-12">
<table class="table table-bordered">
    <tr>
        <td class="text-center bold">
            Decisiones
        </td>
    </tr>
    <tr>
        <td>
            {*listQuestions*}
        </td>
    </tr>
</table>
</div>
</div>
<div class="row">
<div class="col-12">
<table class="table table-bordered">
    <tr>
        <td class="text-center bold">
            Compromisos
        </td>
    </tr>
    <tr>
        <td>
            {*listTasks*}
        </td>
    </tr>
</table>
</div>
</div>
<div class="row">
<div class="col-12">
<table class="table table-bordered">
    <tr>
        <td>
            <span class="bold">SECRETARIO:</span>
            <span>{*showSecretary*}</span>
        </td>
        <td>
            <span class="bold">PRESIDENTE:</span>
            <span>{*showPresident*}</span>
        </td>
    </tr>
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
            "exportar" => "mpdf",
            "funcionario_idfuncionario" => 1,
            "fecha" => "2020-01-22 11:37:36",
            "imagen" => null,
            "detalle" => "0",
            "tipo_edicion" => 0,
            "item" => "0",
            "serie_idserie" => 0,
            "ayuda" => null,
            "font_size" => "11",
            "banderas" => "asunto_padre",
            "mostrar_pdf" => 0,
            "orden" => null,
            "firma_digital" => 0,
            "fk_categoria_formato" => $category,
            "funcion_predeterminada" => "0",
            "paginar" => "0",
            "pertenece_nucleo" => 0,
            "permite_imprimir" => 1,
            "descripcion_formato" => "acta",
            "proceso_pertenece" => 0,
            "version" => 1,
            "documentacion" => null,
            "mostrar_tipodoc_pdf" => 0,
            "publicar" => 0,
            "module" => "actas"
        ];
        $agendamiento = [
            "nombre" => "agendamiento_acta",
            "etiqueta" => "Agendamiento de reunión",
            "cod_padre" => 0,
            "contador_idcontador" => 16,
            "nombre_tabla" => "ft_agendamiento_acta",
            "ruta_mostrar" => "app/modules/back_actas/formatos/agendamiento_acta/mostrar.php",
            "ruta_editar" => "views/modules/actas/dist/schedule/index.html",
            "ruta_adicionar" => "views/modules/actas/dist/schedule/index.html",
            "encabezado" => null,
            "cuerpo" => "<p>{*asunto*}</p>\n",
            "pie_pagina" => null,
            "margenes" => "25,25,25,25",
            "orientacion" => null,
            "papel" => "Letter",
            "exportar" => "mpdf",
            "funcionario_idfuncionario" => 1,
            "fecha" => "2020-01-22 13:41:49",
            "imagen" => null,
            "detalle" => "0",
            "tipo_edicion" => 0,
            "item" => "0",
            "serie_idserie" => 0,
            "ayuda" => null,
            "font_size" => "11",
            "banderas" => "asunto_padre",
            "mostrar_pdf" => 0,
            "orden" => null,
            "firma_digital" => 0,
            "fk_categoria_formato" => $category,
            "funcion_predeterminada" => "0",
            "paginar" => "0",
            "pertenece_nucleo" => 0,
            "permite_imprimir" => 1,
            "descripcion_formato" => "Agendamiento de reunión",
            "proceso_pertenece" => null,
            "version" => 1,
            "documentacion" => null,
            "mostrar_tipodoc_pdf" => 0,
            "publicar" => null,
            "module" => "actas"
        ];
        $this->connection->insert('formato', $acta);
        $actaId = $this->connection->lastInsertId();

        $this->connection->insert('formato', $agendamiento);
        $agendamientoId = $this->connection->lastInsertId();

        $this->connection->delete('modulo', [
            'nombre' => 'crear_acta'
        ]);

        $row = $this->connection->fetchAll("select idmodulo from modulo where nombre = 'modulo_formatos'");
        $parentModuleId = $row[0]['idmodulo'];

        $this->connection->insert('modulo', [
            'pertenece_nucleo' => 1,
            'nombre' => 'crear_acta',
            'tipo' => '2',
            'imagen' => NULL,
            'etiqueta' => 'Acta',
            'enlace' => "views/modules/actas/views/document/index.php",
            'cod_padre' => $parentModuleId,
            'orden' => 1
        ]);

        $this->connection->delete('modulo', [
            'nombre' => 'crear_agendamiento_acta'
        ]);

        $this->connection->insert('modulo', [
            'pertenece_nucleo' => 1,
            'nombre' => 'crear_agendamiento_acta',
            'tipo' => '2',
            'imagen' => NULL,
            'etiqueta' => 'Agendamiento de reunión',
            'enlace' => "views/modules/actas/dist/schedule/index.html",
            'cod_padre' => $parentModuleId,
            'orden' => 1
        ]);

        $actafields = [
            [
                "formato_idformato" => $actaId,
                "nombre" => "asistentes_externos",
                "etiqueta" => "Asistentes externos",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => "",
                "predeterminado" => null,
                "banderas" => "",
                "etiqueta_html" => "ejecutor",
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
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "autocompletar_funcionario",
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
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e,p",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "textarea_cke",
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
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "i",
                "etiqueta_html" => "funcion",
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
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "i",
                "etiqueta_html" => "hidden",
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
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => "1",
                "banderas" => null,
                "etiqueta_html" => "hidden",
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
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 0,
                "valor" => "",
                "acciones" => "a,e,b",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "hidden",
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
                "tipo_dato" => "datetime",
                "longitud" => null,
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => null,
                "etiqueta_html" => "fecha",
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
                "tipo_dato" => "datetime",
                "longitud" => null,
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => null,
                "etiqueta_html" => "fecha",
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
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => "1",
                "banderas" => null,
                "etiqueta_html" => "hidden",
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
                "nombre" => "fk_agendamiento_act",
                "etiqueta" => "fk act planning",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 0,
                "valor" => "",
                "acciones" => "a,e,b",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "hidden",
                "orden" => 8,
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
                "nombre" => "idft_acta",
                "etiqueta" => "acta",
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "ai,pk",
                "etiqueta_html" => "hidden",
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
                "nombre" => "room",
                "etiqueta" => "Sala",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 0,
                "valor" => "",
                "acciones" => "a,e,b",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "hidden",
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

        $agendamientoFields = [
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "date",
                "etiqueta" => "Fecha y Hora",
                "tipo_dato" => "datetime",
                "longitud" => null,
                "obligatoriedad" => 0,
                "valor" => "",
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => null,
                "etiqueta_html" => "fecha",
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
                "formato_idformato" => $agendamientoId,
                "nombre" => "dependencia",
                "etiqueta" => "DEPENDENCIA DEL CREADOR DEL DOCUMENTO",
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "i",
                "etiqueta_html" => "funcion",
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
                "formato_idformato" => $agendamientoId,
                "nombre" => "documento_iddocumento",
                "etiqueta" => "DOCUMENTO ASOCIADO",
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "i",
                "etiqueta_html" => "hidden",
                "orden" => null,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "encabezado",
                "etiqueta" => "ENCABEZADO",
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => "1",
                "banderas" => null,
                "etiqueta_html" => "hidden",
                "orden" => null,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "firma",
                "etiqueta" => "FIRMAS DIGITALES",
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => "1",
                "banderas" => null,
                "etiqueta_html" => "hidden",
                "orden" => null,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "idft_agendamiento_acta",
                "etiqueta" => "agendamiento_acta",
                "tipo_dato" => "integer",
                "longitud" => "11",
                "obligatoriedad" => 1,
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "ai,pk",
                "etiqueta_html" => "hidden",
                "orden" => null,
                "adicionales" => null,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "state",
                "etiqueta" => "Estado",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => null,
                "valor" => "",
                "acciones" => "a,e,b",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "hidden",
                "orden" => 4,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "Campo hidden",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "subject",
                "etiqueta" => "Asunto",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e,p",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "text",
                "orden" => 2,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => "campo texto",
                "longitud_vis" => null,
                "opciones" => "{\"clase\":\"col-md-12\"}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $agendamientoId,
                "nombre" => "duration",
                "etiqueta" => "Duración",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => 1,
                "valor" => "",
                "acciones" => "a,e,p",
                "ayuda" => "",
                "predeterminado" => "",
                "banderas" => "",
                "etiqueta_html" => "spin",
                "orden" => 5,
                "adicionales" => "",
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ]
        ];


        foreach ($agendamientoFields as $field) {
            $this->connection->insert('campos_formato', $field);
        }

        $this->connection->delete('pantalla_grafico', [
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
            "cod_padre" => $grouper,
            "orden" => '2',
            "color" => NULL
        ]);

        $this->connection->executeQuery("truncate table migrations_actas");
    }

    public function down(Schema $schema): void
    {
    }
}
