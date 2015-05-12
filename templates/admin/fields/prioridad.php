<?php 
      global $post;
      $prioridad = get_post_meta( $post->ID, 'prioridad', true );
?>
<p>
         <label>Prioridad:</label>
         <input type="number" class="input-99" name="prioridad" value="<?php echo  (isset($prioridad))?$prioridad:0; ?>" />
</p>