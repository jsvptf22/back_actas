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
use Saia\controllers\AccionController;
use Saia\models\formatos\Formato;
use Saia\Actas\formatos\acta\FtActa;

JwtController::check($_REQUEST["token"], $_REQUEST["key"]); 

$Formato = new Formato(471);
$documentId=$_REQUEST['documentId'] ?? 0;

$FtActa = FtActa::findByDocumentId($documentId);

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
                    Acta
                </h5>
                <form 
                    name='formulario_formatos' 
                    id='formulario_formatos' 
                    role='form' 
                    autocomplete='off' 
                    >
                    <input type='hidden' name='documento_iddocumento' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'documento_iddocumento',
                $FtActa,
                471
            ) ?>'>
<input type='hidden' name='encabezado' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'encabezado',
                $FtActa,
                471
            ) ?>'>
<input type='hidden' name='firma' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'firma',
                $FtActa,
                471
            ) ?>'>
<input type='hidden' name='idft_acta' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'idft_acta',
                $FtActa,
                471
            ) ?>'>

        <?php
        use Saia\controllers\SessionController;use Saia\core\DatabaseConnection;
        $selected = $FtActa->dependencia ?? '';
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
            <div class="form-group form-group-default required" id="group_asunto">
                <label title="">
                    ASUNTO
                </label>
                <textarea 
                    name="asunto"
                    id="asunto" 
                    rows="3" 
                    class="form-control required"
                ><?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'asunto',
                $FtActa,
                471
            ) ?></textarea>
                
            </div>
            <div class="form-group form-group-default input-group required date" id="group_fecha_inicial">
                <div class="form-input-group">
                    <label for='fecha_inicial' title=''>
                        FECHA INICIAL
                    </label>
                    <input type="text" class="form-control required" id="fecha_inicial" name="fecha_inicial">
                </div>
                <div class='input-group-append'>
                    <span class='input-group-text'>
                        <i class='fa fa-calendar'></i>
                    </span>
                </div>
            </div>
<?php
                $defaultDate = Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                    'fecha_inicial',
                    $FtActa,
                    471
                );
                $defaultDate = Saia\controllers\DateController::convertDate(
                    $defaultDate,
                    'Y-m-d H:i:s',
                    Saia\controllers\DateController::DEFAULT_FORMAT
                );
            ?>        <script type='text/javascript'>
            $(function () {
                let defaultDate = '<?= $defaultDate ?>';
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

                $('#fecha_inicial').datetimepicker(options);

                if(!defaultDate.length){
                    $('#fecha_inicial').data('DateTimePicker').clear();
                }
            });
        </script>
            <div class="form-group form-group-default input-group required date" id="group_fecha_final">
                <div class="form-input-group">
                    <label for='fecha_final' title=''>
                        FECHA FINAL
                    </label>
                    <input type="text" class="form-control required" id="fecha_final" name="fecha_final">
                </div>
                <div class='input-group-append'>
                    <span class='input-group-text'>
                        <i class='fa fa-calendar'></i>
                    </span>
                </div>
            </div>
<?php
                $defaultDate = Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                    'fecha_final',
                    $FtActa,
                    471
                );
                $defaultDate = Saia\controllers\DateController::convertDate(
                    $defaultDate,
                    'Y-m-d H:i:s',
                    Saia\controllers\DateController::DEFAULT_FORMAT
                );
            ?>        <script type='text/javascript'>
            $(function () {
                let defaultDate = '<?= $defaultDate ?>';
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

                $('#fecha_final').datetimepicker(options);

                if(!defaultDate.length){
                    $('#fecha_final').data('DateTimePicker').clear();
                }
            });
        </script>
<input type='hidden' name='estado' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'estado',
                $FtActa,
                471
            ) ?>'>
            <div class='form-group form-group-default form-group-default-select2 required' id='group_asistentes_externos'>
                <label title=''>ASISTENTES EXTERNOS</label>
                <select class="full-width" id='asistentes_externos' multiple="multiple" required ></select>
                <input type="hidden" name="asistentes_externos">
            </div>
            <script>
                $(function(){
                    var baseUrl = $('script[data-baseurl]').data('baseurl');
                    var select = $("#asistentes_externos");
                    select.select2({
                        minimumInputLength: 0,
                        language: 'es',
                        ajax: {
                            url: baseUrl+'app/tercero/autocompletar.php',
                            dataType: 'json',
                            data: function(params) {
                                return {
                                    term: params.term,
                                    key: localStorage.getItem('key'),
                                    token: localStorage.getItem('token')
                                };
                            },
                            processResults: function(response) {
                                let crearNuevo = {id: 9999, text: 'Crear tercero', showModal: true};
                                let importar = {id: 9999, text: 'Importar terceros', showModalImport: true};
                                response.data.push(crearNuevo,importar);
                                return { results: response.data}
                            }
                        }                        
                    }).on('select2:selecting', function (e) {
                        
                        let data = e.params.args.data;

                        if(data.showModal){
                            e.preventDefault();
                            openModal();
                        }
                        if(data.showModalImport){
                            e.preventDefault();
                            openModalImport(e.target.id);
                        }
                    }).on('change', function(){
                        let value = $(this).val().join(',');
                        $("[name='asistentes_externos']").val(value);
                    });

                    $('#group_asistentes_externos')
                        .off('click', '.select2-selection__choice')
                        .on('click', '.select2-selection__choice', function (e){
                            if($(e.target).hasClass('select2-selection__choice__remove')){
                                return;
                            }
                            let title = $(this).attr('title');
                            let item = $("#asistentes_externos").select2('data').find(i => i.text == title);
                            openModal(item, $(this));
                        });

                    function openModal(item = 0, selectedNode = null){
                        top.topModal({
                            url: 'views/tercero/formularioDinamico.php',
                            params: {
                                fieldId : 9068,
                                id: item.id
                            },
                            title: 'Tercero',
                            buttons: {
                                success: {
                                    label: 'Continuar',
                                    class: 'btn btn-complete'
                                },
                                cancel: {
                                    label: 'Cerrar',
                                    class: 'btn btn-danger'
                                }
                            },
                            onSuccess: function(data) {                                
                                if(selectedNode){
                                    selectedNode.find('span').trigger('click');
                                }

                                select.select2('close');
                                var option = new Option(data.text, data.id, true, true);
                                select.append(option).trigger('change');
                                top.closeTopModal();
                            }
                        });
                    }

                    function openModalImport(idCampo){
                                let options = {
                                    url: `views/tercero/importarTerceros.php`,
                                    params: {idCampo:idCampo},
                                    centerAlign: false,
                                    size: 'modal-lg',
                                    title: 'Importar terceros',
                                    buttons: {
                                        success: {
                                            label: 'Continuar',
                                            class: 'btn btn-complete'
                                        },
                                        cancel: {
                                            label: 'Cerrar',
                                            class: 'btn btn-danger'
                                        }
                                    },
                                    onSuccess: function (response){successImport(response);}
                                    };
                                top.topModal(options);
                    }

                    function successImport(response){
                        let tercero = JSON.parse(response.data);
                            tercero.forEach(datos => {
                                var option = new Option(
                                    datos.nombre,
                                    datos.id,
                                    true,
                                    true
                                );
                                $('#' + response.campo)
                                    .append(option)
                                    .trigger('change');
                            });
                    }
                });
            </script>            <script>
                $(function(){
                    var baseUrl = $('script[data-baseurl]').data('baseurl');
                    var select = $("#asistentes_externos");
                    var selected = "<?= $FtActa->asistentes_externos ?>".split(',');
                    
                    selected.forEach(id => {
                        $.ajax({
                            type: 'POST',
                            dataType: 'json',
                            url: baseUrl+'app/tercero/autocompletar.php',
                            data: {
                                defaultUser: id,
                                key: localStorage.getItem('key'),
                                token: localStorage.getItem('token')
                            },
                            success: function(response) {
                                response.data.forEach(u => {
                                    var option = new Option(u.text, u.id, true, true);
                                    select
                                        .append(option)
                                        .trigger('change');
                                });
                            }
                        });
                    })
                });
            </script>
        <div class='form-group form-group-default required' id='group_asistentes_internos'>
            <label title=''>ASISTENTES INTERNOS</label>
            <div class="col pl-0 pr-1" id="asistentes_internos_ua"></div>

            <input class='required' type='hidden' id='asistentes_internos' name='asistentes_internos'>
        </div>
        <script>
            $(function () {
                let baseUrl = $('script[data-baseurl]').data('baseurl');
                let users = null;

                if (typeof Users == 'undefined') {
                    $.getScript(baseUrl + 'views/assets/theme/assets/js/cerok_libraries/users/users.js', r => {
                        showUserComponent();
                    });
                } else {
                    showUserComponent();
                }

                function showUserComponent() {
                    users = new Users({
                        selector: '#asistentes_internos_ua',
                        baseUrl: baseUrl,
                        identificator: 'asistentes_internos',
                        change: () => {fillHidden()}
                    });
                    users.setList('<?= $FtActa->asistentes_internos ?>'.split(','))
                }

                function fillHidden(){
                    $('#asistentes_internos').val(users.getList().join(','));
                }
            });
        </script>
<input type='hidden' name='fk_agendamiento_act' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'fk_agendamiento_act',
                $FtActa,
                471
            ) ?>'>
<input type='hidden' name='room' value='<?= Saia\controllers\generador\ComponentFormGeneratorController::callShowValue(
                'room',
                $FtActa,
                471
            ) ?>'>
<input type='hidden' name='campo_descripcion' value='9070'>
					<input type='hidden' name='documentId' value='<?= $documentId ?>'>
					<input type='hidden' id='tipo_radicado' name='tipo_radicado' value='apoyo'>
					<input type='hidden' name='formatId' value='471'>
					<input type='hidden' name='tabla' value='ft_acta'>
					<input type='hidden' name='formato' value='acta'>
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
            $additionalParameters=$FtActa->getRouteParams(FtActa::SCOPE_ROUTE_PARAMS_EDIT); 
        }else{
            $additionalParameters=$FtActa->getRouteParams(FtActa::SCOPE_ROUTE_PARAMS_ADD); 
        }
        $params=array_merge($_REQUEST,$additionalParameters,['baseUrl'=> $baseUrl]);
    ?>
    <script data-baseurl='<?= $baseUrl ?>' >
        $(function() {
            $.getScript('<?= $baseUrl ?>app/modules/back_actas/formatos/acta/funciones.js', () => {
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
        AccionController::ACTION_EDIT,
        AccionController::BEFORE_MOMENT,
        $FtActa ?? null,
        $Formato
    ) ?>
</body>
</html>