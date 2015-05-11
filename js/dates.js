$(function(){
    // Datepicker
    $('input[type=date]').each(function(){
        this.type="text";
    });
    $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy',
            numberOfMonths: 1 
   });
});
$(document).ready(function(){
    $("#fecha_ini").change(function(){
        if($( this ).val() > $("#fecha_fin").val()){
            $( this ).focus();
            alert("La fecha de inicio debe ser menor la de fin");
        }
    });
    
    $("#fecha_fin").change(function(){
        if($("#fecha_ini").val() === "" ){
            $("#fecha_ini").focus();
            alert("Debe seleccionar una fecha de inicio");
        }
        if($( this ).val() < $("#fecha_ini").val()){
            $( this ).focus();
            alert("La fecha fin debe ser mayora la de inicio");
        }
    });
});