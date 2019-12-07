let params = $('#add_edit_script').data('params');

//evento ejecutado en el adicionar
function add(){
   
}

//evento ejecutado en el editar
function edit(data){
    
}

//evento ejecutado en el mostrar
function show(data){
    
}

//evento ejecutado anterior al adicionar
function beforeSendAdd(){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado posterior al adicionar
function afterSendAdd(xhr){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado anterior al editar
function beforeSendEdit(){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado posterior al editar
function afterSendEdit(xhr){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado anterior al devolver o rechazar
function beforeReject(){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado posterior al devolver o rechazar
function afterReject(xhr){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado anterior al confirmar o aprobar
function beforeConfirm(){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

//evento ejecutado posterior al confirmar o aprobar
function afterConfirm(xhr){
    return new Promise((resolve, reject) => {
      resolve();
    });
}

