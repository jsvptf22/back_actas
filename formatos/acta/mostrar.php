<?php
$max_salida = 10;
$rootPath = $ruta = '';

while ($max_salida > 0) {
    if (is_file($ruta . 'sw.js')) {
        $rootPath = $ruta;
        break;
    }

    $ruta .= '../';
    $max_salida--;
}

include_once $rootPath . 'app/vendor/autoload.php';
include_once $rootPath . 'app/modules/actas/formatos/acta/funciones.php';

use Saia\Actas\formatos\acta\FtActa;

try {
    JwtController::check($_REQUEST["token"], $_REQUEST["key"]); 
    
    $documentId = $_REQUEST["documentId"];
    $FtActa = FtActa::findByDocumentId($documentId);
    $Documento = $FtActa->Documento;
    $Formato = $Documento->getFormat();
    
    if(
        !$_REQUEST['mostrar_pdf'] && !$_REQUEST['actualizar_pdf'] && (
            ($_REQUEST["tipo"] && $_REQUEST["tipo"] == 5) ||
            0 == 0
        )
    ): ?>
        <!DOCTYPE html>
        <html>
            <head>
                <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
                <meta charset="utf-8" />
                <meta name="viewport"
                    content="width=device-width, initial-scale=1.0, maximum-scale=10.0, shrink-to-fit=no" />
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta name="apple-touch-fullscreen" content="yes">
                <meta name="apple-mobile-web-app-status-bar-style" content="default">
                <meta content="" name="description" />
                <meta content="" name="Cero K" /> 
            </head>
            <body>
                <div class="container bg-master-lightest mx-0 px-2 px-md-2 mw-100">
                    <div id="documento" class="row p-0 m-0">
                        <div id="pag-0" class="col-12 page_border bg-white">
                            <div class="page_margin_top mb-0" id="doc_header">
                            <?php include_once $rootPath . "formatos/librerias/header_nuevo.php" ?>
                            </div>
                            <div id="pag_content-0" class="page_content">
                                <div id="page_overflow">
                                    <div class="row">
    <div class="col-12">
        <table class="table table-bordered">
            <tr>
            <td>Acta N°</td>
            <td>
               <?= UtilitiesController::formato_numero($FtActa) ?>
            </td>
            <td>Tema / Asunto</td>
            <td colspan="3">
                <?= ComponentFormGeneratorController::callShowValue('asunto',$FtActa,459) ?>
            </td>
            </tr>
            <tr>
            <td>Fecha</td>
            <td><?= ComponentFormGeneratorController::callShowValue('fecha_inicial',$FtActa,459) ?></td>
            <td>Hora Inicio</td>
            <td><?= ComponentFormGeneratorController::callShowValue('fecha_inicial',$FtActa,459) ?></td>
            <td>Hora Final</td>
            <td><?= ComponentFormGeneratorController::callShowValue('fecha_final',$FtActa,459) ?></td>
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
            <span
            v-for="user of getAssistants()"
            v-bind:key="user.id"
            >{{ user.name }},&nbsp;&nbsp;</span
            >
        </td>
        </tr>
        <tr>
        <td>
            Invitados:
            <span v-for="user of getInvited()" v-bind:key="user.id"
            >{{ user.name }},&nbsp;&nbsp;</span
            >
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
            <ul>
            <li
                v-for="topic of documentInformation.topicList"
                v-bind:key="topic.id"
            >
                {{ topic.label }}
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
        <td class="text-center">
            Puntos Tratados / Desarrollo
        </td>
        </tr>
        <tr>
        <td>
            <ul>
            <li
                v-for="item of documentInformation.topicListDescription"
                v-bind:key="item.id"
            >
                <span>{{ getTopicLabel(item.topic) }}</span>
                <br />
                <p>
                {{ item.description }}
                </p>
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
        <td class="text-center">
            Responsabilidades
        </td>
        </tr>
        <tr>
        <td>
        <table v-if="documentInformation.tasks.length" class="table">	
            <tr>
                <td>Tarea</td>
                <td>Responsable</td>
                <td>Ver</td>
            </tr>
            <tr v-for="task of documentInformation.tasks">
                <td>{{task.name}}</td>
                <td>{{getTasksUsers(task.managers)}}</td>
                <td>
                    <button class="btn" v-on:click="openTaskModal(task.id)">
                        <span class="fa fa-eye"></span>
                    </button>
                </td>
            </tr>
        </table>
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
            <span v-if="documentInformation.roles.secretary">{{
            documentInformation.roles.secretary.name
            }}</span>
        </td>
        <td class="firm_square">
            Aprobado por:
            <span v-if="documentInformation.roles.president">{{
            documentInformation.roles.president.name
            }}</span>
        </td>
        </tr>
    </table>
    </div>
</div>
                                </div>
                            </div>
                            <?php include_once $rootPath . "formatos/librerias/footer_nuevo.php" ?>
                        </div> <!-- end page-n -->
                    </div> <!-- end #documento-->
                </div> <!-- end .container -->
            </body>
            <script>
                $(function(){
                    $.getScript('<?= $rootPath ?>app/modules/actas/formatos/acta/funciones.js', () => {
                        show(<?= json_encode($FtActa->getAttributes()) ?>);
                    });
                });
            </script>
        </html>
    <?php else:
        $params = [
            "type" => "TIPO_DOCUMENTO",
            "typeId" => $documentId,
            "exportar" => $Formato->exportar,
            "ruta" => base64_encode($Documento->pdf)
        ];

        if(
            $_REQUEST["actualizar_pdf"] ||
            (
                !$Documento->pdf && (
                    $Formato->mostrar_pdf == 1 ||
                    $_REQUEST['mostrar_pdf']
                )
            )
        ){
            $params["actualizar_pdf"] = 1;
        }

        $url = ABSOLUTE_SAIA_ROUTE . "views/visor/pdfjs/viewer.php?";
        $url.= http_build_query($params);

        echo "<iframe width='100%' frameborder='0' onload='this.height = window.innerHeight - 20' src='{$url}'></iframe>";
    endif; 
} catch (\Throwable $th) {
    die($th->getMessage());
}