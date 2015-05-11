<?php 
      global $post;
      $comision = get_post_meta( $post->ID, 'comision', true );
?>
<p>
         <label>Comision:</label>
         <input type="text" maxlength="180" name="comision" value="<?php echo  (isset($comision))?$comision:""; ?>" style="width: 98.5%;"  />
</p>