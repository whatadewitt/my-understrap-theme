<?php
/* child specific logic can live here */

function theme_enqueue_styles() {
	// Get the theme data
	$the_theme = wp_get_theme();
  $theme_name = get_stylesheet(); // because child theme

  // styles
  wp_enqueue_style( "{$theme_name}-styles", get_stylesheet_directory_uri() . '/css/child-theme.min.css', array(), $the_theme->get( 'Version' ) ); // TODO: update child theme file name
  
  // scripts
  wp_enqueue_script( "{$theme_name}-scripts", get_stylesheet_directory_uri() . '/js/child-theme.min.js', array(), $the_theme->get( 'Version' ), true ); // TODO: update child theme file name
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );