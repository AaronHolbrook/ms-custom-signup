<?php
printf(
	/**
	 * Filter the content of the notification email for new user sign-up.
	 *
	 * Content should be formatted for transmission via wp_mail().
	 *
	 * @since MU
	 *
	 * @param string $content    Content of the notification email.
	 * @param string $user       User login name.
	 * @param string $user_email User email address.
	 * @param string $key        Activation key created in wpmu_signup_user().
	 * @param array  $meta       Signup meta data.
	 */
	apply_filters( 'wpmu_signup_user_notification_email',
		__( "To activate your user, please click the following link:\n\n%s\n\nAfter you activate, you will receive *another email* with your login." ),
		$user, $user_email, $key, $meta
	),
	esc_url( $activation_link )
);