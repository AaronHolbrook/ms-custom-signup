<?php
/**
 * Plugin Name: Multisite Custom Signup
 * Description: The wp-signup.php file is notoriously hard to filter/customize. This plugin essentially rebuilds that file with better filters and more flexibility with modifying how it works.
 * Version:     0.1.0
 * Author:      Aaron Holbrook, 10up
 * Author URI:  http://10up.com
 * License:     GPLv2
 * Text Domain: ms-custom-signup
 * Domain Path: /languages
 */

namespace MS_Custom_Signup;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Useful global constants
define( 'MSCS_VERSION', '0.1.0' );
define( 'MSCS_URL',     plugin_dir_url( __FILE__ ) );
define( 'MSCS_PATH',    dirname( __FILE__ ) . '/' );

/**
 * Main loading function for our custom signup page
 *
 * Hooked to the 'before_signup_form' hook on wp-signup.php
 */
function signup_replace() {

	require_once( MSCS_PATH . 'includes/functions.php' );
	require_once( MSCS_PATH . 'includes/ms-functions.php' );
	require_once( MSCS_PATH . 'includes/new-user.php' );
	require_once( MSCS_PATH . 'includes/new-blog.php' );

	// This replaces the core wp-signup.php file
	include( MSCS_PATH . 'views/signup-form.php' );

	// All done!
	exit;
}
add_action( 'before_signup_form', '\MS_Custom_Signup\signup_replace', 9999 );
