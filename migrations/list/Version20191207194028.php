<?php

declare(strict_types=1);

namespace SAIA\Migrations\Actas;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191207194028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
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
            <tr>
            <td>Acta N°</td>
            <td>
               {*formato_numero*}
            </td>
            <td>Tema / Asunto</td>
            <td colspan="3">
                {*asunto*}
            </td>
            </tr>
            <tr>
            <td>Fecha</td>
            <td>{*fecha_inicial*}</td>
            <td>Hora Inicio</td>
            <td>{*fecha_inicial*}</td>
            <td>Hora Final</td>
            <td>{*fecha_final*}</td>
            </tr>
            <tr>
            <td>Lugar</td>
            <td colspan="5"></td>
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
            Asistentes:
            {*listInternalAssistants*}
        </td>
        </tr>
        <tr>
        <td>
            Invitados:
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
        <td class="text-center">
            Puntos a Tratar / Orden del día
        </td>
        </tr>
        <tr>
        <td>
            {*listTopics*}
        </td>
        </tr>
    </table>
    </div>
</div>
<div class="row">
    <div class="col-12">
    <table class="table table-bordered">
        <tr>
        <td class="text-center">
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
        <td class="text-center">
            Responsabilidades
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
        <td class="firm_square">
            Revisado por:
            {*showSecretary*}
        </td>
        <td class="firm_square">
            Aprobado por:
            {*showPresident*}
        </td>
        </tr>
    </table>
    </div>
</div>
HTML;

        $this->connection->insert('formato', [
            "nombre" => "acta",
            "etiqueta" => "Acta",
            "cod_padre" => 0,
            "contador_idcontador" => 4,
            "nombre_tabla" => "ft_acta",
            "ruta_mostrar" => "app/modules/actas/formatos/acta/mostrar.php",
            "ruta_editar" => "views/modules/actas/views/document/index.php",
            "ruta_adicionar" => "views/modules/actas/views/document/index.php",
            "librerias" => null,
            "estilos" => null,
            "javascript" => null,
            "encabezado" => "1",
            "cuerpo" => $body,
            "pie_pagina" => "4",
            "margenes" => "25,25,25,25",
            "orientacion" => null,
            "papel" => "Letter",
            "exportar" => "mpdf",
            "funcionario_idfuncionario" => 1,
            "fecha" => "2019-12-07 14:13:00",
            "mostrar" => "1",
            "imagen" => null,
            "detalle" => "0",
            "tipo_edicion" => 0,
            "item" => "0",
            "serie_idserie" => 0,
            "ayuda" => null,
            "font_size" => "11",
            "banderas" => "asunto_padre",
            "tiempo_autoguardado" => "300000",
            "mostrar_pdf" => 0,
            "orden" => null,
            "enter2tab" => 0,
            "firma_digital" => 0,
            "fk_categoria_formato" => $category,
            "flujo_idflujo" => null,
            "funcion_predeterminada" => "0",
            "paginar" => "0",
            "pertenece_nucleo" => 0,
            "permite_imprimir" => 1,
            "firma_crt" => null,
            "pos_firma_crt" => null,
            "logo_firma_crt" => null,
            "pos_logo_firma_crt" => null,
            "descripcion_formato" => "acta",
            "proceso_pertenece" => 0,
            "version" => 1,
            "documentacion" => null,
            "mostrar_tipodoc_pdf" => 0,
            "publicar" => 0,
            "module" => "actas"
        ]);
        $formatId = $this->connection->lastInsertId();

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

        $fields = [
            [
                "formato_idformato" => $formatId,
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
                "mascara" => "",
                "adicionales" => "",
                "autoguardado" => 1,
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => "{\"tipo_seleccion\":\"multiple\",\"tipo\":true,\"nombre\":true,\"tipo_identificacion\":true,\"identificacion\":true,\"ciudad\":false,\"titulo\":false,\"direccion\":true,\"telefono\":true,\"correo\":false,\"sede\":false,\"cargo\":true,\"empresa\":true}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => "",
                "adicionales" => "",
                "autoguardado" => 1,
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => "",
                "adicionales" => "",
                "autoguardado" => 1,
                "fila_visible" => 1,
                "placeholder" => "campo texto con formato",
                "longitud_vis" => null,
                "opciones" => "{\"avanzado\":false}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => "",
                "adicionales" => "",
                "autoguardado" => 1,
                "fila_visible" => 1,
                "placeholder" => "Campo hidden",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => "{\"hoy\":true,\"tipo\":\"datetime\"}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => "",
                "longitud_vis" => null,
                "opciones" => "{\"hoy\":true,\"tipo\":\"datetime\"}",
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
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
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => null,
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ],
            [
                "formato_idformato" => $formatId,
                "nombre" => "fk_act_planning",
                "etiqueta" => "fk act planning",
                "tipo_dato" => "string",
                "longitud" => "255",
                "obligatoriedad" => "0",
                "valor" => null,
                "acciones" => "a,e",
                "ayuda" => null,
                "predeterminado" => null,
                "banderas" => "",
                "etiqueta_html" => "hidden",
                "orden" => 8,
                "mascara" => null,
                "adicionales" => null,
                "autoguardado" => 0,
                "fila_visible" => 1,
                "placeholder" => "Campo hidden",
                "longitud_vis" => null,
                "opciones" => null,
                "estilo" => null,
                "listable" => 1
            ]
        ];


        foreach ($fields as $field) {
            $this->connection->insert('campos_formato', $field);
        }
    }

    public function down(Schema $schema): void
    {
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
    }
}
