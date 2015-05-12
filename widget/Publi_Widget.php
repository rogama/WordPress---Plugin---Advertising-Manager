<?php
class PubliWidget extends WP_Widget {

         /**
          * Register widget with WordPress.
          */
         function __construct() {
                  parent::__construct(
                           'publi_widget', // Base ID
                           __('Anuncio de Publicidad', 'text_domain'), // Name
                           array( 'description' => __( 'Muestra un anuncio creado previamente', 'text_domain' ), ) // Args
                  );
         }

 /**
  * Front-end display of widget.
  *
  * @see WP_Widget::widget()
  *
  * @param array $args     Widget arguments.
  * @param array $instance Saved values from database.
  */
 public function widget( $args, $instance ) {
         echo $args['before_widget'];
         
         $ads = self::getAds($instance);

         $totalComisionEuro = 0;
         $totalComisionPercent = 0;
         foreach ($ads as $ad) {
                  $totalComisionEuro += get_post_meta( $ad->ID, 'comision_euro', true );
                  $totalComisionPercent += get_post_meta( $ad->ID, 'comision_percent', true ) ;
         }
         
         $prevPercentEuro = 0;
         $prevPercentPercent = 0;
         foreach ($ads as $ad) {
                  $ad->percentEuro = self::getPercent($ad->ID, "comision_euro", $totalComisionEuro, $prevPercentEuro);
                  $ad->percentPercent = self::getPercent($ad->ID, "comision_percent", $totalComisionPercent, $prevPercentPercent);
                  
                  $prevPercentEuro = $ad->percentEuro["max"];
                  $prevPercentPercent = $ad->percentPercent["max"];
         }

         echo self::getAdContent($ads, mt_rand(0, 100));
         
         echo $args['after_widget'];
 }
/**
 * recogemos todos los anuncios que son del tamaño establecido, estan activos, y además no han caducado
 * 
 * @return array
 */
function getAds($instance){
         return  get_posts(array(
                           'post_type' => 'publi',
                           'posts_per_page' => -1,
                           'tax_query' => array(
                                    array(
                                      'taxonomy' => 'sizes',
                                      'field' => 'id',
                                      'terms' => $instance[ 'size' ]
                                    )
                           ),
                           'meta_query' => array(
                                               array(
                                    'relation' => 'OR',
                                             array(
                                                      'key'     => 'fecha_fin',
                                                      'value'   => date("d/m/Y"),
                                                      'compare' => '>='
                                             ),
                                             array(
                                                      'key'     => 'fecha_fin',
                                                      'value'   => "",
                                                      'compare' => '='
                                             )),
                                    'relation' => 'AND',
                                             array(
                                                      'key'     => 'fecha_ini',
                                                      'value'   => date("d/m/Y"),
                                                      'compare' => '<='
                                             ),
                                             array(
                                                      'key'     => 'activado',
                                                      'value'   => "1",
                                                      'compare' => '='
                                             )
                           )
                         ));
                         
         // Oops, $results has nothing, or something we did not expect
         // Show the query
//         return new WP_Query( $arg );
//         var_dump($results->request, "esto");
 }
 /**
  * return array of min and max percent to ad
  * 
  * @param int $postId
  * @param string $field
  * @param int $total
  * @param int $prevPercent
  *
  *  @return array
  */
 function getPercent($postId, $field, $total, $prevPercent){
          $value = get_post_meta( $postId, $field, true );
          $percent =  (($value)? $value : 0) * 100 / (($total !== 0)? $total : 1);
          return array("min" => $prevPercent, "max" => $prevPercent + $percent);
 }
 /**
  * return a String of post_Content of ad into percent
  * 
  * @param array $ads
  * @param int $percent
  * @return string
  */
 function getAdContent($ads, $percent){
         $selectComision = mt_rand(0, 1);
         foreach ($ads as $ad) {
                  if($selectComision === 0 &&
                                    $ad->percentEuro["min"] <= $percent &&
                                    $ad->percentEuro["max"] >= $percent){
                           return $ad->post_content;
                           
                  }elseif($selectComision === 1 &&
                                    $ad->percentPercent["min"] <= $percent &&
                                    $ad->percentPercent["max"] >= $percent){
                           return $ad->post_content;
                  }
         }
 }
 /**
  * Back-end widget form.
  *
  * @see WP_Widget::form()
  *
  * @param array $instance Previously saved values from database.
  */
 public function form( $instance ) {
         $previousSize=0;
         if ( isset( $instance[ 'size' ] ) ) {
                  $previousSize = $instance[ 'size' ];
         }
  
         $sizes = get_terms( "sizes" );
         ?>
         <p>
                <label for="<?php echo $this->get_field_id( 'size' ); ?>"><?php _e( 'Select size:' ); ?></label> 
                <select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
                         <?php  foreach ($sizes as $size) : ?>
                                  <option value="<?php echo $size->term_id; ?>" <?php echo ($previousSize == $size->term_id)?"selected":"" ?>><?php echo $size->name; ?></option>
                         <?php endforeach; ?>
                </select>
         </p>
  <?php 
 }

 /**
  * Sanitize widget form values as they are saved.
  *
  * @see WP_Widget::update()
  *
  * @param array $new_instance Values just sent to be saved.
  * @param array $old_instance Previously saved values from database.
  *
  * @return array Updated safe values to be saved.
  */
 public function update( $new_instance, $old_instance ) {
  $instance = array();
  $instance['size'] = ( ! empty( $new_instance['size'] ) ) ? strip_tags( $new_instance['size'] ) : '';

  return $instance;
 }

} // class Foo_Widget

function publi_widget() {
    register_widget( 'PubliWidget' );
}
add_action( 'widgets_init', 'publi_widget' );