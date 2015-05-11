<?php
/*
Plugin Name: Gestor de Publicidad
Description: Administra y gestiona los bloques de Publi de tu Site
Author: ROG@MA
Version: 0.1
*/

add_action( 'admin_enqueue_scripts', 'child_add_scripts' );

/**
 * Register and enqueue a script that does not depend on a JavaScript library.
 */
function child_add_scripts() {
         wp_enqueue_script( 'jquery-ui-datepicker' , array( 'jquery' ));
         wp_enqueue_script( 'dates' , plugin_dir_url( __FILE__ ) . '/js/dates.js' );
         
         wp_register_style(   'jquery-ui-datepicker', 'http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
         wp_enqueue_style( 'jquery-ui-datepicker' );
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
		'sices',
		'publi',
		array(
			'label' => __( 'Tamaños' ),
			'rewrite' => array( 'slug' => 'sices' )
		)
	);
}

add_action( 'add_meta_boxes', 'publiMetas' );
function publiMetas(){
      add_meta_box("publi_meta_comision", "Comision", "publi_meta_comision", "publi", "side", "high");
      add_meta_box("publi_meta_fechas", "Activado", "publi_meta_fechas", "publi", "side", "high");
}
 
function publi_meta_comision() {
      include 'templates/admin/fields/comision.php';
}

function publi_meta_fechas() {
      include 'templates/admin/fields/fechas.php';
}

add_action( 'save_post', 'publi_save_metas' );
function publi_save_metas( $post_id ) {
      if ( isset( $_POST['comision'] ) ) {
          update_post_meta( $post_id, 'comision',  $_POST['comision'] );
          update_post_meta( $post_id, 'activado',  $_POST['activado'] );
          update_post_meta( $post_id, 'fecha_ini',  $_POST['fecha_ini'] );
          update_post_meta( $post_id, 'fecha_fin',  $_POST['fecha_fin'] );
      }     
}