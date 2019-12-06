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
include_once $rootPath . 'app/modules/actas/formatos/tema/funciones.php';

use Saia\Actas\formatos\tema\FtTema;

JwtController::check($_REQUEST["token"], $_REQUEST["key"]); 

$Formato = new Formato(460);


$params = json_encode([
    'formatId' => $Formato->getPK(),
    'documentId' => $_REQUEST['iddoc'] ?? 0,
    'baseUrl' => $rootPath
] + $_REQUEST);
?>
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="utf-8" />
    <title>SGDA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=10.0, shrink-to-fit=no" />
    <meta name="apple-mobile-web-app-capable" content="yes">

    
</head>

<body>
    <div class='container-fluid container-fixed-lg col-12' style="overflow: auto;height:100%">
        <div class='card card-default'>
            <div class='card-body'>
                <h5 class='text-black w-100 text-center'>
                    tema
                </h5>
                <form 
                    name='formulario_formatos' 
                    id='formulario_formatos' 
                    role='form' 
                    autocomplete='off' 
                    >
                    <input type='hidden' name='idft_tema' value=''>
<input type='hidden' name='encabezado' value='1'>
<input type='hidden' name='firma' value='1'>
<input type='hidden' name='ft_acta' value='<?= $_REQUEST['padre'] ?>'>
        <?php
        $selected = isset($FtTema) ? $FtTema->dependencia : '';
        $query = Model::getQueryBuilder();
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

        echo "<div class='form-group' id='group_dependencie'>";
    
        if ($total > 1) {
            echo "<select class='full-width' name='dependencia' id='dependencia' required>";
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
            echo "<input class='required' type='hidden' value='{$roles[0]['iddependencia_cargo']}' id='dependencia' name='dependencia'>
                <label class ='form-control'>{$roles[0]["nombre"]} - ({$roles[0]["cargo"]})</label>";
        } else {
            throw new Exception("Error al buscar la dependencia", 1);
        }
        
        echo "</div>";
        ?>
            <div class="form-group form-group-default required" id="group_nombre">
                <label title="">
                    NOMBRE
                </label>
                <textarea 
                    name="nombre"
                    id="nombre" 
                    rows="3" 
                    class="form-control required"
                ></textarea>
                <script>
                $(function(){
                    CKEDITOR.plugins.addExternal('saveTemplate', 'http://localhost/saia_2019/saia/views/assets/theme/assets/js/cerok_libraries/ckeditorPlugins/saveTemplate/');
                    CKEDITOR.replace('nombre', {
                        extraPlugins: 'templates,saveTemplate',
                        templates: 'user',
                    });

                    let editor = CKEDITOR.instances['nombre'];
                    editor.on( 'key', function( evt ) {
                        setTimeout(() => $('#nombre').val(editor.getData()), 0);
                    } );
                });
            </script>
            </div>
            <div class="form-group form-group-default required" id="group_desarrollo">
                <label title="">
                    DESARROLLO
                </label>
                <textarea 
                    name="desarrollo"
                    id="desarrollo" 
                    rows="3" 
                    class="form-control required"
                ></textarea>
                <script>
                $(function(){
                    CKEDITOR.plugins.addExternal('saveTemplate', 'http://localhost/saia_2019/saia/views/assets/theme/assets/js/cerok_libraries/ckeditorPlugins/saveTemplate/');
                    CKEDITOR.replace('desarrollo', {
                        extraPlugins: 'templates,saveTemplate',
                        templates: 'user',
                    });

                    let editor = CKEDITOR.instances['desarrollo'];
                    editor.on( 'key', function( evt ) {
                        setTimeout(() => $('#desarrollo').val(editor.getData()), 0);
                    } );
                });
            </script>
            </div>
<input type='hidden' name='estado' value=''>
<input type='hidden' name='anterior' value='<?= $_REQUEST['anterior'] ?>'>
<input type='hidden' name='campo_descripcion' value='8924'>
<input type='hidden' name='iddoc' value='<?= $_REQUEST['iddoc'] ?? null ?>'>
<input type='hidden' id='tipo_radicado' name='tipo_radicado' value='apoyo'>
<input type='hidden' name='formatId' value='460'>
<input type='hidden' name='tabla' value='ft_tema'>
<input type='hidden' name='formato' value='tema'>
<div class='form-group px-0 pt-3' id='form_buttons'><button class='btn btn-complete mr-2' id='save_item' type='button'>Guardar</button><button class='btn btn-success' id='add_item' type='button'>Adicionar otro</button></div>
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
    <?= users() ?>
    <script id="add_edit_script" data-params='<?= $params ?>'>
        $(function() {
            $.getScript('<?= $rootPath ?>app/modules/actas/formatos/tema/funciones.js');

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
                        $("#form_buttons").find('button,#spiner').toggle();
                        
                        executeEvents();
                    },
                    invalidHandler: function() {
                        $("#save_document").show();
                        $("#boton_enviando").remove();
                    }
                })
                $("#formulario_formatos").trigger('submit');
            }

            function executeEvents(){
                let params = $('#add_edit_script').data('params');

                (params.documentId ? beforeAdd() : beforeEdit())
                    .then(r => {
                        sendData()
                            .then(r => {
                                (params.documentId ? afterAdd() : afterEdit())
                                    .then(response => {
                                        callback(response.data);
                                    })
                                    .catch(response => {
                                        top.notification({
                                            message: response.message,
                                            type: 'error',
                                            title: 'Error!'
                                        });
                                    })
                            })
                    }).catch(response => {
                        top.notification({
                            type: 'error',
                            message: response
                        });
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
                        '<?= $rootPath ?>app/documento/guardar_ft.php',
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
        });
    </script>
    <?= AccionController::execute(
        FuncionFormatoEvento::ACTION_ADD,
        FuncionFormatoEvento::BEFORE_MOMENT,
        $FtTema ?? null,
        $Formato
    ) ?>
</body>
</html>