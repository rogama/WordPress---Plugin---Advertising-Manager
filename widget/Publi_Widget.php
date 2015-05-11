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
         if ( ! empty( $instance['size'] ) ) {
          echo $args['before_size'] . apply_filters( 'widget_title', $instance['size'] ). $args['after_size'];
         }
         echo __( 'Este es mi primer Widget!!!', 'text_domain' );
         echo $args['after_widget'];
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