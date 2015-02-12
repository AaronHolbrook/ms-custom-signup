<?php

namespace MS_Custom_Signup;

/**
 * Display user registration form
 *
 * @since MU
 *
 * @param string       $user_name  The entered username
 * @param string       $user_email The entered email address
 * @param string|array $errors
 */
function show_user_form( $user_name = '', $user_email = '', $errors = '' ) {

	// User name
	printf( '<label for="user_name">%s</label>', esc_html__( 'Username:', 'ms-custom-signup' ) );

	// Print error messages related to the user_name
	if ( $errmsg = $errors->get_error_message( 'user_name' ) ) {
		printf( '<p class="error">%s</p>', esc_html( $errmsg ) );
	} ?>

	<input name="user_name" type="text" id="user_name" value="<?php echo esc_attr( $user_name ); ?>" maxlength="60"/>
	<br/>
	<?php esc_html_e( '(Must be at least 4 characters, letters and numbers only.)' ); ?>

	<label for="user_email"><?php _e( 'Email&nbsp;Address:' ) ?></label>
	<?php

	if ( $errmsg = $errors->get_error_message( 'user_email' ) ) {
		printf( '<p class="error">%s</p>', esc_html( $errmsg ) );
	} ?>

	<input name="user_email" type="email" id="user_email" value="<?php echo esc_attr( $user_email ) ?>"
	       maxlength="200"/>
	<br/>
	<?php

	esc_html_e( 'We send your registration email to this address. (Double-check your email address before continuing.)', 'ms-custom-signup' );

	if ( $errmsg = $errors->get_error_message( 'generic' ) ) {
		printf( '<p class="error">%s</p>', esc_html( $errmsg ) );
	}
	/**
	 * Fires at the end of the user registration form on the site sign-up form.
	 *
	 * @since 3.0.0
	 *
	 * @param array $errors An array possibly containing 'user_name' or 'user_email' errors.
	 */
	do_action( 'signup_extra_fields', $errors );
}

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
	$errors           = $filtered_results['errors']; ?>

	<h2><?php printf( __( 'Get your own %s account in seconds' ), get_current_site()->site_name ) ?></h2>
	<form id="setupform" method="post" action="wp-signup.php" novalidate="novalidate">
		<input type="hidden" name="stage" value="validate-user-signup"/>
		<?php
		/** This action is documented in wp-signup.php */
		do_action( 'signup_hidden_fields', 'validate-user' );

		show_user_form( $user_name, $user_email, $errors ); ?>

		<p>
			<?php
			if ( 'blog' === $active_signup ) {
				printf( '<input id="signupblog" type="hidden" name="signup_for" value="blog"/>' );
			} else if ( 'user' === $active_signup ) {
				printf( '<input id="signupblog" type="hidden" name="signup_for" value="user"/>' );
			} else { ?>
				<input id="signupblog" type="radio" name="signup_for"
				       value="blog" <?php checked( $signup_for, 'blog' ); ?> />
				<label class="checkbox" for="signupblog"><?php _e( 'Gimme a site!' ) ?></label>
				<br/>
				<input id="signupuser" type="radio" name="signup_for"
				       value="user" <?php checked( $signup_for, 'user' ); ?> />
				<label class="checkbox" for="signupuser"><?php _e( 'Just a username, please.' ) ?></label>
			<?php } ?>
		</p>

		<p class="submit">
			<input type="submit" name="submit" class="submit"
			       value="<?php esc_attr_e( 'Next' ) ?>"/>
		</p>
	</form>
<?php
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

	ob_start();

	if ( file_exists( $template ) ) {
		include $template;
	} else {
		include MCSC_PATH . 'views/confirm-user-signup.php';
	}

	/** This action is documented in wp-signup.php */
	do_action( 'signup_finished' );
}