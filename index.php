<?php
/*
Plugin Name: Gestor de Publicidad
Plugin URI
Description: Administra y gestiona los bloques de Publi de tu Site
Author: ROG@MA
Author URI: http://www.rogamainformatica.es
Version: 0.0.3
License: GPL2
*/
include_once 'widget/Publi_Widget.php';
add_action( 'admin_enqueue_scripts', 'publi_add_scripts' );

/**
 * Register and enqueue a script that does not depend on a JavaScript library.
 */
function publi_add_scripts() {
         wp_enqueue_script( 'jquery');
         wp_enqueue_script( 'jquery-ui-datepicker' , array( 'jquery' ));
         wp_enqueue_script( 'dates' , plugin_dir_url( __FILE__ ) . '/js/dates.js' );
         
         wp_register_style(   'jquery-ui-datepicker', 'http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
         wp_enqueue_style( 'jquery-ui-datepicker' );
         
         wp_register_style(   'personal-styles', plugin_dir_url( __FILE__ ) . '/css/style.css');
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
add_action('save_post', 'publi_completion_validator', 20, 2);
function publi_save_metas( $post_id, $post) {
         if ( $post->post_type != 'publi' ) {return $post_id;}
         
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
         if ( $post->post_type != 'publi' ) {return $post_id;}

         $fechaIni = get_post_meta( $post_id, 'fecha_ini', true );
         $fechaFin = get_post_meta( $post_id, 'fecha_fin', true );

         // on attempting to publish - check for completion and intervene if necessary
         if ( ( isset( $_POST['publish'] ) || isset( $_POST['save'] ) ) && $_POST['post_status'] == 'publish' ) {
                  //  don't allow publishing while any of these are incomplete
                  validateDates($post_id, $fechaIni, $fechaFin) ;
                  
                  if ( !get_post_meta( $post_id, 'comision_euro', true ) && !get_post_meta( $post_id, 'comision_percent', true )) {
                           publi_post_to_pendig($post_id);
                           // filter the query URL to change the published message
                           add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "7", $location);' ) );
                  }
         }
}

function validateDates($post_id, $fechaIni, $fechaFin) {
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
                           add_filter( 'redirect_post_location', create_function( '$location','return add_query_arg("message", "6", $location);' ) );
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
                           case 6:
                                    echo"<div class=\"error\"> <p>La fecha de Fin debe ser mayor a hoy</p></div>";
                                    break;
                           case 7:
                                    echo"<div class=\"error\"> <p>Debe rellenar una comision</p></div>";
                                    break;
                           default:
                                    break;
                  }
         }
}
