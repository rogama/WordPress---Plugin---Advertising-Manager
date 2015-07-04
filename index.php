<?php
/*
Plugin Name: Gestor de Publicidad
Plugin URI
Description: Administra y gestiona los bloques de Publi de tu Site
Author: ROG@MA
Author URI: http://www.rogamainformatica.es
Version: 0.0.4
License: GPL2
*/
include_once 'widget/Publi_Widget.php';
include_once 'functions.php';

add_action( 'admin_enqueue_scripts', 'publi_add_scripts' );

/**
 * Register and enqueue a script that does not depend on a JavaScript library.
 */
function publi_add_scripts() {
         wp_enqueue_script( 'jquery');
         wp_enqueue_script( 'jquery-ui-datepicker' , array( 'jquery' ));
         wp_enqueue_script( 'dates' , plugin_dir_url( __FILE__ ) . '/js/dates.js' );
         
         wp_register_style( 'jquery-ui-datepicker', plugin_dir_url( __FILE__ ) . 'css/JQuery/jquery-ui.min.css');
         wp_enqueue_style( 'jquery-ui-datepicker' );
         
         wp_register_style( 'personal-styles', plugin_dir_url( __FILE__ ) . '/css/style.css');
         wp_enqueue_style( 'personal-styles' );
}



// Hook into the 'init' action
add_action( 'init', 'gestorPubli' );
function gestorPubli() {
	$labels = array(
		'name'                => 'Anuncios',
		'singular_name'       => 'Anuncio',
        'add_new'             => 'Crear nuevo anuncio',
		'add_new_item'        => 'Crear nuevo anuncio',
		'edit_item'           => 'Editar Anuncio',
		'view_item'           => 'Ver Anuncio',
		'search_items'        => 'Buscar Anuncios',
		'not_found'           => 'Anuncio no encontrado',
		'not_found_in_trash'  =>'Anuncio no encontrado en papelera'	);
	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'public'              => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page' );
	register_post_type( 'publi', $args );

}

add_action( 'init', 'publi_register_taxonomies' );
function publi_register_taxonomies() {
	register_taxonomy(
		'proveedor',
		'publi',
		array(
			'label' => __( 'Proveedor' ),
			'rewrite' => array( 'slug' => 'proveedor' )
		)
	);
   	register_taxonomy(
		'sizes',
		'publi',
		array(
			'label' => __( 'Tamaños' ),
			'rewrite' => array( 'slug' => 'sizes' )
		)
	);
}

add_action( 'add_meta_boxes', 'publiMetas' );
function publiMetas(){
         add_meta_box("publi_meta_comision", "Comision", "publi_meta_comision", "publi", "side", "high");
         add_meta_box("publi_meta_fechas", "Activado", "publi_meta_fechas", "publi", "side", "high");
         add_meta_box("publi_meta_prioridad", "Prioridad", "publi_meta_prioridad", "publi", "side", "high");
}
 
function publi_meta_comision() {
         include 'templates/admin/fields/comision.php';
}

function publi_meta_fechas() {
         include 'templates/admin/fields/fechas.php';
}

function publi_meta_prioridad() {
         include 'templates/admin/fields/prioridad.php';
}

add_action( 'save_post', 'publi_save_metas', 10, 2);
add_action( 'save_post', 'publi_completion_validator', 20, 2);
function publi_save_metas( $post_id, $post) {
         if ( $post->post_type != 'publi' ) {
             
             return $post_id;
         }
         
         if ( isset($_POST['comision']) || isset($_POST['activado']) || isset($_POST['fecha_ini']) || isset($_POST['fecha_fin']) ) {
                  update_post_meta( $post_id, 'comision_euro', filter_input(INPUT_POST, "comision_euro", FILTER_SANITIZE_STRING));
                  update_post_meta( $post_id, 'comision_percent', filter_input(INPUT_POST, "comision_percent", FILTER_SANITIZE_STRING));
                  update_post_meta( $post_id, 'activado', filter_input(INPUT_POST, "activado", FILTER_VALIDATE_BOOLEAN));
                  update_post_meta( $post_id, 'fecha_ini', filter_input(INPUT_POST, "fecha_ini", FILTER_SANITIZE_STRING));
                  update_post_meta( $post_id, 'fecha_fin', filter_input(INPUT_POST, "fecha_fin", FILTER_SANITIZE_STRING));
                  update_post_meta( $post_id, 'prioridad', filter_input(INPUT_POST, "prioridad", FILTER_VALIDATE_INT));
         }     
}


function publi_completion_validator($post_id, $post) {
         if ( $post->post_type != 'publi' ) {
             
             return $post_id;
         }
         
         $fechaIni = get_post_meta( $post_id, 'fecha_ini', true );
         $fechaFin = get_post_meta( $post_id, 'fecha_fin', true );        

         // on attempting to publish - check for completion and intervene if necessary
         if ( ( isset( $_POST['publish'] ) || isset( $_POST['save'] ) ) && $_POST['post_status'] == 'publish' ) {
                  //  don't allow publishing while any of these are incomplete
                  validateDates($post_id, $fechaIni, $fechaFin) ;
                  
                  if ( !get_post_meta( $post_id, 'comision_euro', true ) && 
                          !get_post_meta( $post_id, 'comision_percent', true )) {
                           publi_post_to_pendig($post_id);
                           // filter the query URL to change the published message
                           add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "7", $location);' ) );
                  }
         }
}

function validateDates($post_id, $fechaIni, $fechaFin) {
    global $post;
    var_dump($post); die;
    if ( $post->post_type != 'publi' ) {

         return $post_id;
     }
     if ( empty($fechaIni )) {
              publi_post_to_pendig($post_id);
              // filter the query URL to change the published message
              add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "4", $location);' ) );
     }
     if ( !empty($fechaFin )) {
              if($fechaFin < $fechaIni){
                       publi_post_to_pendig($post_id);
                       add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "5", $location);' ) );
              }
              if($fechaFin < date("d/m/Y")){
                       publi_post_to_pendig($post_id);
                       add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "8", $location);' ) );
              }
     }
}

function publi_post_to_pendig($post_id){
    global $wpdb;
    $wpdb->update( $wpdb->posts, array( 'post_status' => 'pending' ), array( 'ID' => $post_id ) );
}

add_action( 'admin_notices', 'publi_post_error_admin_message' );
function publi_post_error_admin_message() {
    if ( isset( $_GET['message'] ) ) {
        switch ($_GET['message'] ) {
        case 4:
            echo"<div class=\"error\"> <p>No se ha introducido fecha de inicio</p></div>";
            break;
        case 5:
            echo"<div class=\"error\"> <p>La fecha de Fin debe ser mayor a la de inicio</p></div>";
            break;
        case 7:
            echo"<div class=\"error\"> <p>Debe rellenar una comision</p></div>";
            break;
         case 8:
            echo"<div class=\"error\"> <p>La fecha de Fin debe ser mayor a hoy</p></div>";
            break;
        default:
            break;
        }
    }
}

function publi_shortcode( $atts ) {
         $ads = getAds(get_term_by( 'name', $atts['size'], 'sizes'));
         $totalComision = getPercents($ads);
         echo getAdContent(addPercentToAd($ads, $totalComision), mt_rand(0, 100));
}
add_shortcode( 'publi-tag', 'publi_shortcode' );

/**
 * Add custom Columns
 */
add_filter( 'manage_edit-publi_columns', 'publi_custom_columns' ) ;
function publi_custom_columns( $columns ) {
	$custom = array(
		'comision_euro' => __( 'Comision €' ),
        'comision_percent' => __( 'Comision %' ),
        'activado' => __( 'Activate' ),
        'proveedor' => __( 'Proveedor' ),
        'sizes' => __( 'sizes' )
	);
    return array_merge($columns, $custom);
}

add_action( 'manage_publi_posts_custom_column', 'publi_custom_columns_content', 10, 2 );
function publi_custom_columns_content( $column, $post_id ) {
	global $post;
    if ( $post->post_type != 'publi' ) {return $post_id;}

	switch( $column ) {
		case 'comision_euro' :
			$duration = get_post_meta( $post_id, 'comision_euro', true );
			if ( empty( $duration ) ){
                echo __( 'No Commision' );
            }else{
                echo $duration . " €";
            }
			break;
		case 'comision_percent' :
			$duration = get_post_meta( $post_id, 'comision_percent', true );
			if ( empty( $duration ) ){
                echo __( 'No Commision' );
            }else{
                echo $duration . " %";
            }
			break;
        case 'activado' :
            echo '<input type="checkbox" ' . ((get_post_meta( $post_id, 'activado', true ))?'checked':'') . ' disabled>';
			break;
        case 'proveedor' :
			$terms = get_the_terms( $post_id, 'proveedor' );
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'proveedor' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'proveedor', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else {
				_e( 'No Provider' );
			}
			break;
        case 'sizes' :
			$terms = get_the_terms( $post_id, 'sizes' );
			if ( !empty( $terms ) ) {
				$out = array();
				foreach ( $terms as $term ) {
					$out[] = sprintf( '<a href="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => $post->post_type, 'sizes' => $term->slug ), 'edit.php' ) ),
						esc_html( sanitize_term_field( 'name', $term->name, $term->term_id, 'sizes', 'display' ) )
					);
				}
				echo join( ', ', $out );
			}
			else {
				_e( 'No Sizes' );
			}
			break;
		default :
			break;
	}
}

add_filter( 'manage_edit-publi_sortable_columns', 'publi_custom_columns_sort' );
function publi_custom_columns_sort( $columns ) {
	$columns['comision_euro'] = "comision_euro";
    $columns['comision_percent'] = __( 'Comision %' );
    $columns['activado'] = __( 'Activate' );
    $columns['proveedor'] = __( 'Proveedor' );
    $columns['sizes'] = __( 'sizes' );

	return $columns;
}

/* Only run our customization on the 'edit.php' page in the admin. */
add_action( 'load-edit.php', 'publi_edit_load' );

function publi_edit_load() {
	add_filter( 'request', 'publi_sort_ads' );
}


function publi_sort_ads( $vars ) {

	if ( isset( $vars['post_type'] ) && 'publi' == $vars['post_type'] ) {
		if ( isset( $vars['orderby'] ) && 'comision_euro' == $vars['orderby'] ) {
			$vars = array_merge(
				$vars,
				array(
					'meta_key' => 'comision_euro',
					'orderby' => 'meta_value_num'
				)
			);
		}
	}

	return $vars;
}add_shortcode( 'publi-tag', 'publi_shortcode' );