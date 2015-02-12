<?php

namespace MS_Custom_Signup;

/**
 * Validate user signup name and email
 *
 * @since MU
 *
 * @return array Contains username, email, and error messages.
 */
function validate_user_form() {
	return wpmu_validate_user_signup( $_POST['user_name'], $_POST['user_email'] );
}

/**
 * Setup the new user signup process
 *
 * @since MU
 *
 * @param string $user_name  The username
 * @param string $user_email The user's email
 * @param string|array  $errors
 */
function signup_user( $user_name = '', $user_email = '', $errors = '' ) {
	global $active_signup;

	if ( ! is_wp_error( $errors ) ) {
		$errors = new \WP_Error();
	}

	$signup_for = isset( $_POST['signup_for'] ) ? esc_html( $_POST['signup_for'] ) : 'blog';

	$signup_user_defaults = array(
		'user_name'  => $user_name,
		'user_email' => $user_email,
		'errors'     => $errors,
	);

	$active_signup = get_site_option( 'registration', 'none' );

	/**
	 * Filter the default user variables used on the user sign-up form.
	 *
	 * @since 3.0.0
	 *
	 * @param array $signup_user_defaults {
	 *                                    An array of default user variables.
	 *
	 * @type string $user_name            The user username.
	 * @type string $user_email           The user email address.
	 * @type array  $errors               An array of possible errors relevant to the sign-up user.
	 * }
	 */
	$filtered_results = apply_filters( 'signup_user_init', $signup_user_defaults );
	$user_name        = $filtered_results['user_name'];
	$user_email       = $filtered_results['user_email'];
	$errors           = $filtered_results['errors'];

	$template = apply_filters( '\MS_Custom_Signup\signup_user_template', false );

	if ( file_exists( $template ) ) {
		include $template;
	} else {
		include MSCS_PATH . 'views/signup-user-form.php';
	}
}

/**
 * Validate the new user signup
 *
 * @since MU
 *
 * @return bool True if new user signup was validated, false if error
 */
function validate_user_signup() {
	$result     = validate_user_form();
	$user_name  = $result['user_name'];
	$user_email = $result['user_email'];
	$errors     = $result['errors'];

	if ( $errors->get_error_code() ) {
		signup_user( $user_name, $user_email, $errors );

		return false;
	}

	if ( 'blog' == $_POST['signup_for'] ) {
		signup_blog( $user_name, $user_email );

		return false;
	}

	/** This filter is documented in wp-signup.php */
	wpmu_signup_user( $user_name, $user_email, apply_filters( 'add_signup_meta', array() ) );

	confirm_user_signup( $user_name, $user_email );

	return true;
}

/**
 * New user signup confirmation
 *
 * @since MU
 *
 * @param string $user_name  The username
 * @param string $user_email The user's email address
 */
function confirm_user_signup( $user_name, $user_email ) {

	$template = apply_filters( '\MS_Custom_Signup\confirm_user_signup', false );

	if ( file_exists( $template ) ) {
		include $template;
	} else {
		include MCSC_PATH . 'views/confirm-user-signup.php';
	}

	/** This action is documented in wp-signup.php */
	do_action( 'signup_finished' );
}