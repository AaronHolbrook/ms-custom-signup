<?php
/**
 * Display user registration form
 *
 * @since MU
 *
 * @param string       $user_name  The entered username
 * @param string       $user_email The entered email address
 * @param string|array $errors
 */
namespace MS_Custom_Signup; ?>

<form id="setupform" method="post" action="wp-signup.php" novalidate="novalidate">
	<input type="hidden" name="stage" value="validate-user-signup"/>
	<?php
	/** This action is documented in wp-signup.php */
	do_action( 'signup_hidden_fields', 'validate-user' );

	/**
	 * Username input
	 */
	?>
	<p>
		<label for="user_name">
			<?php esc_html_e( 'Username:', 'ms-custom-signup' ); ?><br>
			<input name="user_name" type="text" id="user_name" class="input" size="25" value="<?php echo esc_attr( $user_name ); ?>" maxlength="60"/>
		</label>
	</p>
	<?php
	// Print error messages related to the user_name
	if ( $errmsg = $errors->get_error_message( 'user_name' ) ) {
		printf( '<p class="error">%s</p>', esc_html( $errmsg ) );
	} ?>
	<p><?php esc_html_e( '(Must be at least 4 characters, letters and numbers only.)', 'ms-custom-signup' ); ?></p>
	<br class="clear" />

	<?php
	/**
	 * User Email input
	 */
	?>
	<p>
		<label for="user_email">
			<?php esc_html_e( 'Email Address:', 'ms-custom-signup' ); ?><br>
			<input name="user_email" type="email" id="user_email" class="input" size="25" value="<?php echo esc_attr( $user_email ) ?>"
			       maxlength="200"/>
		</label>
	</p>
	<?php
	if ( $errmsg = $errors->get_error_message( 'user_email' ) ) {
		printf( '<p class="error">%s</p>', esc_html( $errmsg ) );
	} ?>
	<p><?php esc_html_e( 'We send your registration email to this address. (Double-check your email address before continuing.)', 'ms-custom-signup', 'ms-custom-signup' ); ?></p>
	<br class="clear" />

	<?php
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
	?>

	<p>
		<?php
		if ( 'blog' === $active _signup ) {
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

	<?php
	/**
	 * Fires following the 'E-mail' field in the user registration form.
	 *
	 * @since 2.1.0
	 */
	do_action( 'register_form' );
	?>

	<br class="clear" />

	<p class="submit">
		<input type="submit" name="submit" class="button button-primary button-large"
		       value="<?php esc_attr_e( 'Register' ) ?>"/>
	</p>

</form>