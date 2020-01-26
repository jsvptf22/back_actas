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

use Saia\controllers\JwtController;
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
                            <?php include_once $rootPath . "views/formatos/librerias/header_nuevo.php" ?>
                            </div>
                            <div id="pag_content-0" class="page_content">
                                <div id="page_overflow">
                                    <div class="row">
    <div class="col-12">
        <table class="table table-bordered">
            <tr>
                <td class="bold">Acta N°</td>
                <td>
                    <?= Saia\controllers\UtilitiesController::formato_numero($FtActa) ?>
                </td>
            </tr>
            <tr>
                <td class="bold">Tema / Asunto</td>
                <td colspan="3">
                    <?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue('asunto',$FtActa,482) ?>
                </td>
            </tr>
            <tr>
                <td class="bold">Inicio</td>
                <td>
                    <?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue('fecha_inicial',$FtActa,482) ?>
                </td>
            </tr>
            <tr>
                <td class="bold">Fin</td>
                <td><?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue('fecha_final',$FtActa,482) ?></td>
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
                    <?= $FtActa->listInternalAssistants() ?>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="bold">Invitados:</span>
                    <?= $FtActa->listExternalAssistants() ?>        
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
                        <?= $FtActa->listTopics() ?>
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
                    <?= $FtActa->listTopicDescriptions() ?>
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
                    <?= $FtActa->listQuestions() ?>
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
                    <?= $FtActa->listTasks() ?>
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
                    <span><?= $FtActa->showSecretary() ?></span>
                </td>
                <td>
                    <span class="bold">PRESIDENTE:</span>
                    <span><?= $FtActa->showPresident() ?></span>
                </td>
            </tr>
        </table>
    </div>
</div>

                                </div>
                            </div>
                            <?php include_once $rootPath . "views/formatos/librerias/footer_nuevo.php" ?>
                        </div> <!-- end page-n -->
                    </div> <!-- end #documento-->
                </div> <!-- end .container -->
            </body>
            <?php
                $additionalParameters=$FtActa->getRouteParams(FtActa::SCOPE_ROUTE_PARAMS_SHOW);
                $params=array_merge($_REQUEST,$additionalParameters);
            ?>
            <script>
                $(function(){
                    $.getScript('<?= ABSOLUTE_SAIA_ROUTE ?>app/modules/back_actas/formatos/acta/funciones.js', () => {
                        window.routeParams=<?= json_encode($params) ?>;
                        show(<?= json_encode($params) ?>)
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