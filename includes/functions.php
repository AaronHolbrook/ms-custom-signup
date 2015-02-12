<?php

namespace MS_Custom_Signup;



/**
 * Template loader to load our custom email template instead of just passing a string to the filter
 *
 * @param $user
 * @param $user_email
 * @param $key
 * @param $meta
 *
 * @return string
 */
function get_user_activate_email( $user, $user_email, $key, $meta ) {

	$activation_link = site_url( 'wp-activate.php?key=' . $key );

	// Allow a template to be assigned here
	$message_template = apply_filters( '\MS_Custom_Signup\user_activate_email_template', false );

	ob_start();

	// If the template exists then load it - otherwise fall back to our template
	if ( file_exists( $message_template ) ) {
		include $message_template;
	}

	// Ok - no template found or set, let's load our fallback
	else {
		include( MSCS_PATH . 'views/email-activate.php' );
	}

	return ob_get_clean();
}