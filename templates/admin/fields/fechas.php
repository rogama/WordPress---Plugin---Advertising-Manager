<?php 
      global $post;
      $fecha_ini = get_post_meta( $post->ID, 'fecha_ini', true );
      $fecha_fin = get_post_meta( $post->ID, 'fecha_fin', true );
      $activado = get_post_meta( $post->ID, 'activado', true );
?>

<p>
         <input type="hidden" name="activado" value="0">
         <input type="checkbox" id="activado" name="activado" <?php echo  (isset($activado) && $activado == 1)?"checked":""; ?> value="1" />
         <label for="activado">Anuncio activado</label>
</p>
<p>
         <label>Fecha de Inicio:</label>
         <input type="date" maxlength="180" id="fecha_ini" name="fecha_ini" class="custom_date datepicker" value="<?php echo  (isset($fecha_ini))?$fecha_ini:""; ?>" style="width: 98.5%;"  />
</p>
<p>
         <label>Fecha de Fin:</label>
         <input type="date" maxlength="180" id="fecha_fin" name="fecha_fin" class="custom_date datepicker" value="<?php echo  (isset($fecha_fin))?$fecha_fin:""; ?>" style="width: 98.5%;"  />
</p>