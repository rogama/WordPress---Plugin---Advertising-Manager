<?php 
      global $post;
      $comision_euro = get_post_meta( $post->ID, 'comision_euro', true );
      $comision_percent = get_post_meta( $post->ID, 'comision_percent', true );
?>
<p>
         <label>Comision:</label>
         <input type="text" maxlength="180" name="comision_euro" class="input-90" value="<?php echo  (isset($comision_euro))?$comision_euro:""; ?>" /> €
         <input type="text" maxlength="180" name="comision_percent" class="input-90" value="<?php echo  (isset($comision_percent))?$comision_percent:""; ?>"  /> %
</p>