        <div class="row">
            <div class="col-12">
                <div class="form-group form-group-default">
                    <label>Asunto:</label>
                    <input class="form-control" name="bqCampo_ft@subject" type="text">
                    
                    <input type="hidden" name="bqCondicional_subject" value="like">
                    <input type="hidden" name="bqComparador_subject" value="y" />
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group form-group-default input-group">
                    <div class="form-input-group">
                        <label>Fecha y Hora inicial:</label>
                        <input name="bqCampo_ft@date_x" type="text" class="form-control" placeholder="Seleccione.." id="date_x">
                        <input name="bqComparador_date" type="hidden" value="y" />
                        <input name="bqTipo_date_x" type="hidden" value="datetime">

                    </div>
                    <div class="input-group-append ">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group form-group-default input-group">
                    <div class="form-input-group">
                        <label>Fecha y Hora final:</label>
                        <input name="date_y" type="text" class="form-control" placeholder="Seleccione.." id="date_y">
                    </div>
                    <div class="input-group-append ">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="form-group form-group-default">
                    <label>Duración:</label>
                    <input class="form-control" name="bqCampo_ft@duration" type="number">
                    
                    <input type="hidden" name="bqCondicional_duration" value="=">
                    <input type="hidden" name="bqComparador_duration" value="y" />
                </div>
            </div>
        </div>
<script>
            $(document).ready(function(){
                        $('#date_x,#date_y').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD HH:mm:ss',
        });
            })
            </script>