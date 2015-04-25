<?php
/**
 * Replace select functions from core to alter their ability slightly
 */

namespace MS_Custom_Signup;

/**
 * Record user signup information for future activation.
 *
 * This function is used when user registration is open but
 * new site registration is not.
 *
 * @since MU
 *
 * @param string $user       The user's requested login name.
 * @param string $user_email The user's email address.
 * @param array  $meta       By default, this is an empty array.
 */
function wpmu_signup_user( $user, $user_email, $meta = array() ) {
	global $wpdb;

	// Format data
	$user       = preg_replace( '/\s+/', '', sanitize_user( $user, true ) );
	$user_email = sanitize_email( $user_email );
	$key        = substr( md5( time() . rand() . $user_email ), 0, 16 );
	$meta       = serialize( $meta );

	$wpdb->insert( $wpdb->signups, array(
		'domain'         => '',
		'path'           => '',
		'title'          => '',
		'user_login'     => $user,
		'user_email'     => $user_email,
		'registered'     => current_time( 'mysql', true ),
		'activation_key' => $key,
		'meta'           => $meta,
	) );

	wpmu_signup_user_notification( $user, $user_email, $key, $meta );

	// Allow custom functionality upon a user signing up successfully
	do_action( 'MS_Custom_Signup\wpmu_signup_user', $user, $user_email, $meta, $_REQUEST );
}

/**
 * Notify user of signup success.
 *
 * This is the notification function used when no new site has
 * been requested.
 *
 * Filter 'wpmu_signup_user_notification' to bypass this function or
 * replace it with your own notification behavior.
 *
 * Filter 'wpmu_signup_user_notification_email' and
 * 'wpmu_signup_user_notification_subject' to change the content
 * and subject line of the email sent to newly registered users.
 *
 * @since MU
 *
 * @param string $user       The user's login name.
 * @param string $user_email The user's email address.
 * @param string $key        The activation key created in wpmu_signup_user()
 * @param array  $meta       By default, an empty array.
 *
 * @return bool
 */
function wpmu_signup_user_notification( $user, $user_email, $key, $meta = array() ) {

	/**
	 * Filter whether to bypass the email notification for new user sign-up.
	 *
	 * @since MU
	 *
	 * @param string $user       User login name.
	 * @param string $user_email User email address.
	 * @param string $key        Activation key created in wpmu_signup_user().
	 * @param array  $meta       Signup meta data.
	 */
	if ( ! apply_filters( 'wpmu_signup_user_notification', $user, $user_email, $key, $meta ) ) {
		return false;
	}

	// Send email with activation link.
	$admin_email = get_site_option( 'admin_email' );
	if ( $admin_email == '' ) {
		$admin_email = 'support@' . $_SERVER['SERVER_NAME'];
	}

	/**
	 * Filter to modify the admin email for new user notification emails
	 *
	 * @param string $admin_email Administrator/From email address
	 */
	$admin_email = apply_filters( '\MS_Custom_Signup\notification_email_admin_email', $admin_email );

	$from_name = get_site_option( 'site_name' ) == '' ? 'WordPress' : esc_html( get_site_option( 'site_name' ) );

	/**
	 * Filter to modify the from name for new user notification emails
	 *
	 * @param string $from_name Name that the email appears from
	 */
	$from_name = apply_filters( '\MS_Custom_Signup\notification_email_from_name', $from_name );

	/**
	 * Filter to modify the notification email content type
	 *
	 * @param string content type
	 */
	$content_type = apply_filters( '\MS_Custom_Signup\notification_email_content_type', 'text/plain' );

	/**
	 * Build out the message headers for the email
	 */
	$message_headers  = 'From: "' . esc_html( $from_name ) . '" <' . esc_attr( $admin_email ) . ">\n ";
	$message_headers .= 'Content-Type: ' . esc_attr( $content_type ) . "\n ";
	$message_headers .= 'charset="' . esc_attr( get_option( 'blog_charset' ) ) . "\"\n ";

	/**
	 * Get our email message from a template
	 */
	$message = get_user_activate_email( $user, $user_email, $key, $meta );

	$subject = sprintf(
		/**
		 * Filter the subject of the notification email of new user signup.
		 *
		 * @since MU
		 *
		 * @param string $subject    Subject of the notification email.
		 * @param string $user       User login name.
		 * @param string $user_email User email address.
		 * @param string $key        Activation key created in wpmu_signup_user().
		 * @param array  $meta       Signup meta data.
		 */
		apply_filters( 'wpmu_signup_user_notification_subject',
			__( '[%1$s] Activate %2$s' ),
			$user, $user_email, $key, $meta
		),
		$from_name,
		$user
	);
	wp_mail( $user_email, wp_specialchars_decode( $subject ), $message, $message_headers );

	return true;
}