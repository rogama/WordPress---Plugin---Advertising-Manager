<?php 
      global $post;
      $prioridad = get_post_meta( $post->ID, 'prioridad', true );
?>
<p>
         <label>Prioridad:</label>
         <input type="number" maxlength="180" name="prioridad" value="<?php echo  (isset($prioridad))?$prioridad:0; ?>" style="width: 98.5%;"  />
</p>