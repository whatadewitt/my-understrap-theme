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

// echo out a message in the footer so we can ensure the site is running
function ensure_site_status() {
    echo "<!--STATUS:OK-->";
}
add_action( 'wp_footer', 'ensure_site_status' );

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

// todo: ensure all the "required" plugins are activated (cleaner than mu-plugins)

// child theme specific logic
require_once('child-functions.php');

// at the end of the day, this may be worth moving to an mu-plugin to avoid folks from potentially breaking things

// this is where the release info will be stored...
$api_result = false;

// get the release info
function get_repo_release_info() {
	global $github_access_token, $github_repo_name;

	// Only do this once
	if ( !empty( $api_result ) ) {
		return;
	}

	// Query the GitHub API
	$url = "https://api.github.com/repos/whatadewitt/{$github_repo_name}/releases";

	// We need the access token for private repos
	if ( !empty( $github_access_token ) ) {
		$url = add_query_arg( array( 'access_token' => $github_access_token ), $url );
	}

	// Get the results
	$api_result = wp_remote_retrieve_body( wp_remote_get( $url ) );
	if ( !empty( $api_result ) ) {
		$api_result = @json_decode( $api_result );
	}

	// Use only the latest release
	if ( is_array( $api_result ) ) {
		$api_result = $api_result[0];
	}
}

// this will simply remove the "update now" option on a theme
// this will force even administrators to follow a proper flow
// for updating themes.
function theme_pre_set_transient_update_theme( $transient ) {
	$theme_name = get_stylesheet(); // because child theme...
	error_log("HIT");

	// if( empty( $transient->checked[$theme_name] ) ) {
	// 	return $transient;
	// }

	if ( empty( $transient->checked ) ) {
		return $transient;
	}

	get_repo_release_info();
	$do_update = version_compare( $api_result->tag_name, $transient->checked[$theme_name] );

	if ( $do_update == 1 ) {
		$obj = new stdClass();
		$obj->slug = $theme_name;
		$obj->new_version = $this->api_result->tag_name;
		$obj->url = $this->plugin_data['PluginURI'];
		$obj->package = false;
		$transient->response[$theme_name] = $obj;
	}

	return $transient;
}
add_filter( 'pre_set_site_transient_update_themes', 'theme_pre_set_transient_update_theme' );