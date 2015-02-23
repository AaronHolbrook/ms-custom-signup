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
	} // Ok - no template found or set, let's load our fallback
	else {
		include( MSCS_PATH . 'views/email-activate.php' );
	}

	return ob_get_clean();
}

/**
 * Prints signup_header via wp_head
 *
 * @since MU
 * @todo review to see if still needed, i do not think so
 */
function do_signup_header() {
	/**
	 * Fires within the head section of the site sign-up screen.
	 *
	 * @since 3.0.0
	 */
	do_action( 'signup_header' );
}

add_action( 'wp_head', 'do_signup_header' );

/**
 * Prints styles for front-end Multisite signup pages
 *
 * @since MU
 * @todo review to see if still needed, i do not think so
 */
function wpmu_signup_stylesheet() {
	?>
	<style type="text/css">
		.mu_register {
			width: 90%;
			margin: 0 auto;
		}

		.mu_register form {
			margin-top: 2em;
		}

		.mu_register .error {
			font-weight: 700;
			padding: 10px;
			color: #333333;
			background: #FFEBE8;
			border: 1px solid #CC0000;
		}

		.mu_register input[type="submit"],
		.mu_register #blog_title,
		.mu_register #user_email,
		.mu_register #blogname,
		.mu_register #user_name {
			width: 100%;
			font-size: 24px;
			margin: 5px 0;
		}

		.mu_register .prefix_address,
		.mu_register .suffix_address {
			font-size: 18px;
			display: inline;
		}

		.mu_register label {
			font-weight: 700;
			font-size: 15px;
			display: block;
			margin: 10px 0;
		}

		.mu_register label.checkbox {
			display: inline;
		}

		.mu_register .mu_alert {
			font-weight: 700;
			padding: 10px;
			color: #333333;
			background: #ffffe0;
			border: 1px solid #e6db55;
		}
	</style>
<?php
}

/**
 * Output the login page header.
 *
 * @param string   $title    Optional. WordPress login Page title to display in the `<title>` element.
 *                           Default 'Log In'.
 * @param string   $message  Optional. Message to display in header. Default empty.
 * @param \WP_Error $wp_error Optional. The error to pass. Default empty.
 */
function login_header( $title = 'Log In', $message = '', $wp_error = '' ) {
	global $error, $interim_login, $action;

	// Don't index any of these forms
	add_action( 'login_head', 'wp_no_robots' );

	if ( wp_is_mobile() ) {
		add_action( 'login_head', 'wp_login_viewport_meta' );
	}

	if ( empty( $wp_error ) ) {
		$wp_error = new \WP_Error();
	}

	// Shake it!
	$shake_error_codes = array(
		'empty_password',
		'empty_email',
		'invalid_email',
		'invalidcombo',
		'empty_username',
		'invalid_username',
		'incorrect_password',
	);
	/**
	 * Filter the error codes array for shaking the login form.
	 *
	 * @since 3.0.0
	 *
	 * @param array $shake_error_codes Error codes that shake the login form.
	 */
	$shake_error_codes = apply_filters( 'shake_error_codes', $shake_error_codes );

	if ( $shake_error_codes && $wp_error->get_error_code() && in_array( $wp_error->get_error_code(), $shake_error_codes ) ) {
		add_action( 'login_head', 'wp_shake_js', 12 );
	}
	?><!DOCTYPE html>
	<!--[if IE 8]>
	<html xmlns="http://www.w3.org/1999/xhtml" class="ie8" <?php language_attributes(); ?>>
	<![endif]-->
	<!--[if !(IE 8) ]><!-->
	<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
	<!--<![endif]-->
	<head>
		<meta http-equiv="Content-Type"
		      content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>"/>
		<title><?php bloginfo( 'name' ); ?> &rsaquo; <?php echo $title; ?></title>
		<?php

		wp_admin_css( 'login', true );

		/*
		 * Remove all stored post data on logging out.
		 * This could be added by add_action('login_head'...) like wp_shake_js(),
		 * but maybe better if it's not removable by plugins
		 */
		if ( 'loggedout' == $wp_error->get_error_code() ) {
			?>
			<script>
				if ( "sessionStorage" in window ) {
					try {
						for ( var key in sessionStorage ) {
							if ( key.indexOf( "wp-autosave-" ) != - 1 ) {
								sessionStorage.removeItem( key )
							}
						}
					} catch ( e ) {
					}
				};
			</script>
		<?php
		}

		/**
		 * Enqueue scripts and styles for the login page.
		 *
		 * @since 3.1.0
		 */
		do_action( 'login_enqueue_scripts' );
		/**
		 * Fires in the login page header after scripts are enqueued.
		 *
		 * @since 2.1.0
		 */
		do_action( 'login_head' );

		if ( is_multisite() ) {
			$login_header_url   = network_home_url();
			$login_header_title = get_current_site()->site_name;
		} else {
			$login_header_url   = __( 'https://wordpress.org/' );
			$login_header_title = __( 'Powered by WordPress' );
		}

		/**
		 * Filter link URL of the header logo above login form.
		 *
		 * @since 2.1.0
		 *
		 * @param string $login_header_url Login header logo URL.
		 */
		$login_header_url = apply_filters( 'login_headerurl', $login_header_url );
		/**
		 * Filter the title attribute of the header logo above login form.
		 *
		 * @since 2.1.0
		 *
		 * @param string $login_header_title Login header logo title attribute.
		 */
		$login_header_title = apply_filters( 'login_headertitle', $login_header_title );

		$classes = array( 'login-action-' . $action, 'wp-core-ui' );
		if ( wp_is_mobile() ) {
			$classes[] = 'mobile';
		}
		if ( is_rtl() ) {
			$classes[] = 'rtl';
		}
		if ( $interim_login ) {
			$classes[] = 'interim-login';
			?>
			<style type="text/css">
				html {
					background-color: transparent;
				}
			</style>
			<?php

			if ( 'success' === $interim_login ) {
				$classes[] = 'interim-login-success';
			}
		}
		$classes[] = ' locale-' . sanitize_html_class( strtolower( str_replace( '_', '-', get_locale() ) ) );

		/**
		 * Filter the login page body classes.
		 *
		 * @since 3.5.0
		 *
		 * @param array  $classes An array of body classes.
		 * @param string $action  The action that brought the visitor to the login page.
		 */
		$classes = apply_filters( 'login_body_class', $classes, $action );

		?>
	</head>
	<body class="login <?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<div id="login">
	<h1><a href="<?php echo esc_url( $login_header_url ); ?>" title="<?php echo esc_attr( $login_header_title ); ?>"
	       tabindex="-1"><?php bloginfo( 'name' ); ?></a></h1>
	<?php

	unset( $login_header_url, $login_header_title );

	/**
	 * Filter the message to display above the login form.
	 *
	 * @since 2.1.0
	 *
	 * @param string $message Login message text.
	 */
	$message = apply_filters( 'login_message', $message );
	if ( ! empty( $message ) ) {
		echo $message . "\n";
	}

	// In case a plugin uses $error rather than the $wp_errors object
	if ( ! empty( $error ) ) {
		$wp_error->add( 'error', $error );
		unset( $error );
	}

	if ( $wp_error->get_error_code() ) {
		$errors   = '';
		$messages = '';
		foreach ( $wp_error->get_error_codes() as $code ) {
			$severity = $wp_error->get_error_data( $code );
			foreach ( $wp_error->get_error_messages( $code ) as $error_message ) {
				if ( 'message' == $severity ) {
					$messages .= '	' . $error_message . "<br />\n";
				} else {
					$errors .= '	' . $error_message . "<br />\n";
				}
			}
		}
		if ( ! empty( $errors ) ) {
			/**
			 * Filter the error messages displayed above the login form.
			 *
			 * @since 2.1.0
			 *
			 * @param string $errors Login error message.
			 */
			echo '<div id="login_error">' . apply_filters( 'login_errors', $errors ) . "</div>\n";
		}
		if ( ! empty( $messages ) ) {
			/**
			 * Filter instructional messages displayed above the login form.
			 *
			 * @since 2.5.0
			 *
			 * @param string $messages Login messages.
			 */
			echo '<p class="message">' . apply_filters( 'login_messages', $messages ) . "</p>\n";
		}
	}
}

/**
 * Outputs the footer for the login page.
 *
 * @param string $input_id Which input to auto-focus
 */
function login_footer( $input_id = '' ) {
	global $interim_login;

	// Don't allow interim logins to navigate away from the page.
	if ( ! $interim_login ) : ?>
		<p id="backtoblog">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"
		       title="<?php esc_attr_e( 'Are you lost?' ); ?>">
				<?php printf( __( '&larr; Back to %s' ), get_bloginfo( 'title', 'display' ) ); ?>
			</a>
		</p>
	<?php
	endif; ?>

	</div>
	<?php
	if ( ! empty( $input_id ) ) : ?>
		<script type="text/javascript">
			try {
				document.getElementById( '<?php echo $input_id; ?>' ).focus();
			} catch ( e ) {
			}
			if ( 'function' === typeof wpOnload ) {
				wpOnload();
			}
		</script>
	<?php
	endif; ?>

	<?php
	/**
	 * Fires in the login page footer.
	 *
	 * @since 3.1.0
	 */
	do_action( 'login_footer' ); ?>
	<div class="clear"></div>
	</body>
	</html>
<?php
}

/**
 * Brought over from wp-login.php file, unsure if we even need
 * @todo review to see if needed
 */
function wp_shake_js() {
	if ( wp_is_mobile() ) {
		return;
	}
	?>
	<script type="text/javascript">
		addLoadEvent = function ( func ) {
			if ( typeof jQuery != "undefined" )jQuery( document ).ready( func );
			else if ( typeof wpOnload != 'function' ) {
				wpOnload = func;
			}
			else {
				var oldonload = wpOnload;
				wpOnload = function () {
					oldonload();
					func();
				}
			}
		};
		function s( id, pos ) {
			g( id ).left = pos + 'px';
		}
		function g( id ) {
			return document.getElementById( id ).style;
		}
		function shake( id, a, d ) {
			c = a.shift();
			s( id, c );
			if ( a.length > 0 ) {
				setTimeout( function () {
					shake( id, a, d );
				}, d );
			}
			else {
				try {
					g( id ).position = 'static';
					wp_attempt_focus();
				} catch ( e ) {
				}
			}
		}
		addLoadEvent( function () {
			var p = new Array( 15, 30, 15, 0, - 15, - 30, - 15, 0 );
			p = p.concat( p.concat( p ) );
			var i = document.forms[0].id;
			g( i ).position = 'relative';
			shake( i, p, 20 );
		} );
	</script>
<?php
}

/**
 * Outputs the meta viewport
 */
function wp_login_viewport_meta() {
	printf( '<meta name="viewport" content="width=device-width"/>' );
}

/**
 * Filterable message to display on the signup/registration form
 *
 * @return mixed|void
 */
function get_login_header_message() {
	$message = sprintf( esc_html__( 'Get your own %s account in seconds', 'ms-custom-signup' ), get_current_site()->site_name );

	$message = apply_filters( '\MS_Custom_Signup\login_header_message', $message );

	return sprintf( '<p class="message register">%s</p>', esc_html__( $message ) );
}