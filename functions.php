<?php

// disable auto plugin/theme updates
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );

// security things
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'rest_output_link_wp_head' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

// nginx redirect issue... may be unneccessary...
remove_filter( 'template_redirect', 'redirect_canonical' );

// remove "understrap" styles/scripts... may not want to do this...
function understrap_remove_scripts() {
  wp_dequeue_style( 'understrap-styles' );
  wp_deregister_style( 'understrap-styles' );

  wp_dequeue_script( 'understrap-scripts' );
  wp_deregister_script( 'understrap-scripts' );

  // Removes the parent themes stylesheet and scripts from inc/enqueue.php
}
add_action( 'wp_enqueue_scripts', 'understrap_remove_scripts', 20 );

// this will simply remove the "update now" option on a theme
// this will force even administrators to follow a proper flow
// for updating themes.
function theme_pre_set_transient_update_theme($transient) {
	$theme_name = get_stylesheet(); // because child theme...

	if( empty( $transient->checked[$theme_name] ) ) {
		return $transient;
	}

	// TODO: Check releases versus github...

	$transient->response[$theme_name] = false;

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'theme_pre_set_transient_update_theme' );

// child theme specific logic
require_once('child-functions.php');