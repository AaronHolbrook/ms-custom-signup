<?php
/**
 * Confirm User Signup Template
 *
 * Shows after successful signup, indicating to the user that they should check their email and their account
 * will soon be ready.
 *
 * @param string $user_name
 * @param string $user_email
 */
?>
<h2>
	<?php printf( __( '%s is your new username' ), esc_html( $user_name ) ); ?>
</h2>

<p>
	<?php _e( 'But, before you can start using your new username, <strong>you must activate it</strong>.' ) ?>
</p>

<p>
	<?php printf( __( 'Check your inbox at <strong>%s</strong> and click the link given.' ), $user_email ); ?>
</p>

<p>
	<?php _e( 'If you do not activate your username within two days, you will have to sign up again.' ); ?>
</p>