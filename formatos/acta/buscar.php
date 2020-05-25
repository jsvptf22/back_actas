        <div class="row">
              <div class="col-12">
                  <div class="form-group form-group-default">
                      <label>asunto:</label>
                      <textarea class ="form-control" name="bqCampo_ft@asunto"></textarea>

                      <input type="hidden" name="bqCondicional_asunto" value="like">
                      <input type="hidden" name="bqComparador_asunto" value="y" />
                  </div>
              </div>
          </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group form-group-default input-group">
                    <div class="form-input-group">
                        <label>Fecha inicial inicial:</label>
                        <input name="bqCampo_ft@fecha_inicial_x" type="text" class="form-control" placeholder="Seleccione.." id="fecha_inicial_x">
                        <input name="bqComparador_fecha_inicial" type="hidden" value="y" />
                        <input name="bqTipo_fecha_inicial_x" type="hidden" value="datetime">

                    </div>
                    <div class="input-group-append ">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group form-group-default input-group">
                    <div class="form-input-group">
                        <label>Fecha inicial final:</label>
                        <input name="fecha_inicial_y" type="text" class="form-control" placeholder="Seleccione.." id="fecha_inicial_y">
                    </div>
                    <div class="input-group-append ">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group form-group-default input-group">
                    <div class="form-input-group">
                        <label>Fecha final inicial:</label>
                        <input name="bqCampo_ft@fecha_final_x" type="text" class="form-control" placeholder="Seleccione.." id="fecha_final_x">
                        <input name="bqComparador_fecha_final" type="hidden" value="y" />
                        <input name="bqTipo_fecha_final_x" type="hidden" value="datetime">

                    </div>
                    <div class="input-group-append ">
                        <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group form-group-default input-group">
                    <div class="form-input-group">
                        <label>Fecha final final:</label>
                        <input name="fecha_final_y" type="text" class="form-control" placeholder="Seleccione.." id="fecha_final_y">
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
                    <label>Asistentes externos:</label>
                    <input class="form-control" name="bqCampo_asistentes_externos@asistentes_externos" type="text">
                                        
                    <input type="hidden" name="bqCondicional_asistentes_externos" value="like">
                    <input type="hidden" name="bqComparador_asistentes_externos" value="y" />
                    <input type="hidden" name="bqTipo_asistentes_externos" value="ejecutor" />
                    
                    <input type="hidden" name="bqTabla_asistentes_externos" value="tercero asistentes_externos" />
                    <input type="hidden" name="bqRelacionTabla_asistentes_externos" value="asistentes_externos.idtercero=ft.asistentes_externos" />
                </div>
            </div>
        </div>
<script>
            $(document).ready(function(){
                        $('#fecha_inicial_x,#fecha_inicial_y').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD HH:mm:ss',
        });
        $('#fecha_final_x,#fecha_final_y').datetimepicker({
            locale: 'es',
            format: 'YYYY-MM-DD HH:mm:ss',
        });
            })
            </script>