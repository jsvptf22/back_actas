<?php
$max_salida = 10;
$rootPath = $ruta = "";

while ($max_salida > 0) {
    if (is_file($ruta . "sw.js")) {
        $rootPath = $ruta;
    }

    $ruta .= "../";
    $max_salida --;
}

include_once $rootPath . 'app/vendor/autoload.php';
include_once $rootPath . 'views/assets/librerias.php';

use Saia\controllers\JwtController;
use Saia\controllers\generador\ComponentFormGeneratorController;
use Saia\controllers\AccionController;
use Saia\models\formatos\Formato;
use Saia\Actas\formatos\agendamiento_acta\FtAgendamientoActa;

JwtController::check($_REQUEST["token"], $_REQUEST["key"]); 

$Formato = new Formato(483);
$documentId=$_REQUEST['documentId'] ?? 0;

$FtAgendamientoActa = new FtAgendamientoActa;

?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title>SGDA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=10.0, shrink-to-fit=no" />
    <meta name="apple-mobile-web-app-capable" content="yes">

    <?= jquery() ?><?= bootstrap() ?><?= cssTheme() ?>
</head>

<body>
    <div class='container-fluid container-fixed-lg col-lg-8' style="overflow: auto;height:100vh">
        <div class='card card-default'>
            <div class='card-body'>
                <h5 class='text-black w-100 text-center'>
                    Agendamiento de reunión
                </h5>
                <form 
                    name='formulario_formatos' 
                    id='formulario_formatos' 
                    role='form' 
                    autocomplete='off' 
                    >
                    <input type='hidden' name='encabezado' value='1'>
<input type='hidden' name='firma' value='1'>
<input type='hidden' name='idft_agendamiento_acta' value=''>

        <?php
        use Saia\controllers\SessionController;use Saia\core\DatabaseConnection;
        $selected = $FtAgendamientoActa->dependencia ?? '';
        $query = DatabaseConnection::getQueryBuilder();
        $roles = $query
            ->select("dependencia as nombre, iddependencia_cargo, cargo")
            ->from("vfuncionario_dc")
            ->where("estado_dc = 1 and tipo_cargo = 1 and login = :login")
            ->andWhere(
                $query->expr()->lte('fecha_inicial', ':initialDate'),
                $query->expr()->gte('fecha_final', ':finalDate')
            )->setParameter(":login", SessionController::getLogin())
            ->setParameter(':initialDate', new DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->setParameter(':finalDate', new DateTime(), \Doctrine\DBAL\Types\Type::DATETIME)
            ->execute()->fetchAll();
    
        $total = count($roles);

        if ($total > 1) {

            echo "<div class='form-group form-group-default form-group-default-select2 required' id='group_dependencie'>
            <label>Rol activo</label>
            <select class='full-width select2-hidden-accessible' name='dependencia' id='dependencia' required>";
            foreach ($roles as $row) {
                echo "<option value='{$row["iddependencia_cargo"]}'>
                    {$row["nombre"]} - ({$row["cargo"]})
                </option>";
            }
    
            echo "</select>
                <script>
                $(function (){
                    $('#dependencia').select2();
                    $('#dependencia').val({$selected});
                    $('#dependencia').trigger('change');
                });  
                </script>
            ";
        } else if ($total == 1) {
            echo "<div class='form-group form-group-default required' id='group_dependencie'>
                <input class='required' type='hidden' value='{$roles[0]['iddependencia_cargo']}' id='dependencia' name='dependencia'>
                <label>Rol activo</label>
                <div class='form-group'>
                    <label>{$roles[0]["nombre"]} - ({$roles[0]["cargo"]})</label>
                </div>";
        } else {
            throw new Exception("Error al buscar la dependencia", 1);
        }
        
        echo "</div>";
        ?>
<div class='form-group form-group-default required col-md-12'  id='group_subject'>
            <label title=''>ASUNTO</label>
            <input class='form-control required' type='text' id='subject' name='subject' value='' maxLength='250'/>
        </div>
            <div class="form-group form-group-default input-group  date" id="group_date">
                <div class="form-input-group">
                    <label for='date' title=''>
                        FECHA Y HORA
                    </label>
                    <input type="text" class="form-control " id="date" name="date">
                </div>
                <div class='input-group-append'>
                    <span class='input-group-text'>
                        <i class='fa fa-calendar'></i>
                    </span>
                </div>
            </div>        <script type='text/javascript'>
            $(function () {
                let defaultDate = '<?= date('Y-m-d H:i:s') ?>';
                let options = {
                    locale: 'es',
                    format: 'YYYY-MM-DD HH:mm:ss',
                    defaultDate: defaultDate
                };

                switch ('') {
                    case "lt":
                        options.maxDate = moment().subtract(1, 'd');
                        break;
                    case "lte":
                        options.maxDate = moment();
                        break;
                    case "gt":
                        options.minDate = moment().add(1, 'd');
                        break;
                    case "gte":
                        options.minDate = moment();
                        break;
                }

                $('#date').datetimepicker(options);

                if(!defaultDate.length){
                    $('#date').data('DateTimePicker').clear();
                }
            });
        </script>
<input type='hidden' name='state' value=''>
<div class='form-group form-group-default  col-12 ' id='group_duration'>
<label title='' for='duration'>DURACIóN</label>
<input class='form-control'   type='number' id='duration' name='duration'  value=''>
</div>
<input type='hidden' name='anterior' value='<?= $_REQUEST['anterior'] ?>'>
					<input type='hidden' name='campo_descripcion' value='9148'>
					<input type='hidden' name='documentId' value='<?= $documentId ?>'>
					<input type='hidden' id='tipo_radicado' name='tipo_radicado' value='agendamiento_acta'>
					<input type='hidden' name='formatId' value='483'>
					<input type='hidden' name='tabla' value='ft_agendamiento_acta'>
					<input type='hidden' name='formato' value='agendamiento_acta'>
					<div class='form-group px-0 pt-3' id='form_buttons'><button class='btn btn-complete' id='save_document' type='button'>Continuar</button><div class='progress-circle-indeterminate d-none' id='spiner'></div></div>
                </form>
            </div>
        </div>
    </div>

    <?= jsTheme() ?>
    <?= icons() ?>
    <?= moment() ?>
    <?= select2() ?>
    <?= validate() ?>
    <?= ckeditor() ?>
    <?= jqueryUi() ?>
    <?= fancyTree(true) ?>
    <?= dateTimePicker() ?>
    <?= dropzone() ?>
   
    <?php
        $baseUrl= $rootPath;

        if ($Formato->item){
            $baseUrl = "../../";
            echo users(1);
        }
        else{
            echo users();
        }

        if($documentId){
            $additionalParameters=$FtAgendamientoActa->getRouteParams(FtAgendamientoActa::SCOPE_ROUTE_PARAMS_EDIT); 
        }else{
            $additionalParameters=$FtAgendamientoActa->getRouteParams(FtAgendamientoActa::SCOPE_ROUTE_PARAMS_ADD); 
        }
        $params=array_merge($_REQUEST,$additionalParameters,['baseUrl'=> $baseUrl]);
    ?>
    <script data-baseurl='<?= $baseUrl ?>' >
        $(function() {
            $.getScript('<?= $baseUrl ?>app/modules/back_actas/formatos/agendamiento_acta/funciones.js', () => {
                window.routeParams=<?= json_encode($params) ?>;
                if (+'<?= $documentId ?>') {
                    edit(<?= json_encode($params) ?>)
                } else {
                    add(<?= json_encode($params) ?>)
                }
            });

            $("#add_item").click(function() {
                checkForm((data) => {
                    let options = top.window.modalOptions;
                    options.oldSource = null;
                    top.topModal(options)
                })
            });

            $("#save_item").click(function() {
                checkForm((data) => {                    
                    top.successModalEvent(data);
                })
            });

            $("#save_document").click(function() {
                checkForm((data) => {
                    let route = "<?= $rootPath ?>views/documento/index_acordeon.php?";
                    route += $.param(data);
                    window.location.href = route;
                })
            });

            function checkForm(callback){
                $("#formulario_formatos").validate({
                    ignore: [],
                    errorPlacement: function (error, element) {
                        let node = element[0];

                        if (
                            node.tagName == 'SELECT' &&
                            node.className.indexOf('select2') !== false
                        ) {
                            error.addClass('pl-3');
                            element.next().append(error);
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {                       
                        $("#form_buttons").find('button,#spiner').toggleClass('d-none');
                        
                        executeEvents(callback);
                    },
                    invalidHandler: function() {
                        $("#save_document").show();
                        $("#boton_enviando").remove();
                    }
                });
                $("#formulario_formatos").trigger('submit');
            }

            function executeEvents(callback){
                let documentId = $("[name='documentId']").val();

                (+documentId ? beforeSendEdit() : beforeSendAdd())
                    .then(r => {
                        sendData()
                            .then(requestResponse => {
                                (+documentId ? afterSendEdit(requestResponse) : afterSendAdd(requestResponse))
                                    .then(r => {
                                        callback(requestResponse.data);
                                    })
                                    .catch(message => {
                                        fail(message);
                                    })
                            })
                    }).catch(message => {
                       fail(message);
                    });
            }

            function sendData(){
                return new Promise((resolve, reject) => {
                    let data = $('#formulario_formatos').serialize() + '&' +
                    $.param({
                        key: localStorage.getItem('key'),
                        token: localStorage.getItem('token')
                    });
    
                    $.post(
                        '<?= $baseUrl ?>app/documento/guardar_ft.php',
                        data,
                        function(response) {
                            if (response.success) {
                                resolve(response)
                            } else {
                                reject(response);
                            }
                        },
                        'json'
                    );
                });
            }

            function fail(message){
                $("#form_buttons").find('button,#spiner').toggleClass('d-none');
                top.notification({
                    message: message,
                    type: 'error',
                    title: 'Error!'
                });
            }
        });
    </script>
    <?= AccionController::execute(
        AccionController::ACTION_ADD,
        AccionController::BEFORE_MOMENT,
        $FtAgendamientoActa ?? null,
        $Formato
    ) ?>
</body>
</html>