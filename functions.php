<?php
/**
 * recogemos todos los anuncios que son del tamaño establecido, estan activos, y además no han caducado
 * 
 * @return array
 */
function getAds($sizeTagId){
         return  get_posts(array(
                           'post_type' => 'publi',
                           'posts_per_page' => -1,
                           'tax_query' => array(
                                    array(
                                      'taxonomy' => 'sizes',
                                      'field' => 'id',
                                      'terms' => $sizeTagId
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
 }
 
 function getPercents($ads){
         $totalComision = array();
         $totalComision['euro'] = 0;
         $totalComision['percent'] = 0;
         foreach ($ads as $ad) {
                  $totalComision['euro'] += get_post_meta( $ad->ID, 'comision_euro', true );
                  $totalComision['percent'] += get_post_meta( $ad->ID, 'comision_percent', true ) ;
         }
         
         return $totalComision;
 }
 
 function addPercentToAd($ads, $totalComision){
         $prevPercentEuro = 0;
         $prevPercentPercent = 0;
         foreach ($ads as $ad) {
                  $ad->percentEuro = getPercent($ad->ID, "comision_euro", $totalComision['euro'], $prevPercentEuro);
                  $ad->percentPercent = getPercent($ad->ID, "comision_percent", $totalComision['percent'], $prevPercentPercent);
                  
                  $prevPercentEuro = $ad->percentEuro["max"];
                  $prevPercentPercent = $ad->percentPercent["max"];
         }
         return $ads;
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