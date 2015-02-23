<?php
/**
 * Replaces the core wp-signup.php file
 * Adds more filterable areas
 *
 */
namespace MS_Custom_Signup;

add_action( 'wp_head', 'wp_no_robots' );

if ( is_array( get_site_option( 'illegal_names' ) ) && isset( $_GET['new'] ) && in_array( $_GET['new'], get_site_option( 'illegal_names' ) ) == true ) {
	wp_redirect( network_home_url() );
	die();
}


if ( ! is_multisite() ) {
	wp_redirect( site_url( 'wp-login.php?action=register' ) );
	die();
}

if ( ! is_main_site() ) {
	wp_redirect( network_site_url( 'wp-signup.php' ) );
	die();
}

// Fix for page title
global $wp_query;
$wp_query->is_404 = false;

/**
 * Fires before the site sign-up form.
 *
 * @since 3.0.0
 */
do_action( 'before_signup_form' );

/**
 * Main logic for the signup form
 */
$action = isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : 'login';
$errors = new \WP_Error();

if ( isset( $_GET['key'] ) ) {
	$action = 'resetpass';
}

// validate action so as to default to the login screen
if ( ! in_array( $action, array(
		'postpass',
		'logout',
		'lostpassword',
		'retrievepassword',
		'resetpass',
		'rp',
		'register',
		'login',
	), true ) && false === has_filter( 'login_form_' . $action )
) {
	$action = 'login';
}

nocache_headers();

header( 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=' . get_bloginfo( 'charset' ) );

if ( defined( 'RELOCATE' ) && RELOCATE ) { // Move flag is set
	if ( isset( $_SERVER['PATH_INFO'] ) && ( $_SERVER['PATH_INFO'] != $_SERVER['PHP_SELF'] ) ) {
		$_SERVER['PHP_SELF'] = str_replace( $_SERVER['PATH_INFO'], '', $_SERVER['PHP_SELF'] );
	}

	$url = dirname( set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] ) );
	if ( $url != get_option( 'siteurl' ) ) {
		update_option( 'siteurl', $url );
	}
}

//Set a cookie now to see if they are supported by the browser.
$secure = ( 'https' === parse_url( site_url(), PHP_URL_SCHEME ) && 'https' === parse_url( home_url(), PHP_URL_SCHEME ) );
setcookie( TEST_COOKIE, 'WP Cookie check', 0, COOKIEPATH, COOKIE_DOMAIN, $secure );
if ( SITECOOKIEPATH != COOKIEPATH ) {
	setcookie( TEST_COOKIE, 'WP Cookie check', 0, SITECOOKIEPATH, COOKIE_DOMAIN, $secure );
}

/**
 * Fires when the login form is initialized.
 *
 * @since 3.2.0
 */
do_action( 'login_init' );

/**
 * Fires before a specified login form action.
 *
 * The dynamic portion of the hook name, `$action`, refers to the action
 * that brought the visitor to the login form. Actions include 'postpass',
 * 'logout', 'lostpassword', etc.
 *
 * @since 2.8.0
 */
do_action( 'login_form_' . $action );

$http_post     = ( 'POST' == $_SERVER['REQUEST_METHOD'] );
$interim_login = isset( $_REQUEST['interim-login'] );

if ( ! get_option( 'users_can_register' ) ) {
	wp_redirect( site_url( 'wp-login.php?registration=disabled' ) );
	exit();
}

$registration_redirect = ! empty( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : '';
/**
 * Filter the registration redirect URL.
 *
 * @since 3.0.0
 *
 * @param string $registration_redirect The redirect destination URL.
 */
$redirect_to = apply_filters( 'registration_redirect', $registration_redirect );

/**
 * Render out the login header
 */
login_header( esc_html__( 'Registration Form' ), get_login_header_message(), $errors );

// Main
$active_signup = get_site_option( 'registration', 'none' );

/**
 * Filter the type of site sign-up.
 *
 * @since 3.0.0
 *
 * @param string $active_signup String that returns registration type. The value can be
 *                              'all', 'none', 'blog', or 'user'.
 */
$active_signup = apply_filters( 'wpmu_active_signup', $active_signup );

// Make the signup type translatable.
$i18n_signup['all']  = esc_html_x( 'all', 'Multisite active signup type' );
$i18n_signup['none'] = esc_html_x( 'none', 'Multisite active signup type' );
$i18n_signup['blog'] = esc_html_x( 'blog', 'Multisite active signup type' );
$i18n_signup['user'] = esc_html_x( 'user', 'Multisite active signup type' );

if ( is_super_admin() ) :
	?>
	<div class="mu_alert">
		<?php
		printf( __( 'Greetings Site Administrator! You are currently allowing &#8220;%s&#8221; registrations. To change or disable registration go to your <a href="%s">Options page</a>.' ), $i18n_signup[ $active_signup ], esc_url( network_admin_url( 'settings.php' ) ) );
		?>
	</div>
<?php
endif;

$newblogname = isset( $_GET['new'] ) ? strtolower( preg_replace( '/^-|-$|[^-a-zA-Z0-9]/', '', $_GET['new'] ) ) : null;

if ( 'none' === $active_signup ) {
	_e( 'Registration has been disabled.' );
} else if ( 'blog' === $active_signup && ! is_user_logged_in() ) {
	$login_url = site_url( 'wp-login.php?redirect_to=' . urlencode( network_site_url( 'wp-signup.php' ) ) );
	printf( __( 'You must first <a href="%s">log in</a>, and then you can create a new site.' ), esc_url( $login_url ) );
} else {
	$stage = isset( $_POST['stage'] ) ? $_POST['stage'] : 'default';
	switch ( $stage ) {
		case 'validate-user-signup' :
			if ( 'all' === $active_signup || 'blog' === $_POST['signup_for'] && 'blog' === $active_signup || 'user' === $_POST['signup_for'] && 'user' === $active_signup ) {
				validate_user_signup();
			} else {
				_e( 'User registration has been disabled.' );
			}
			break;
		case 'validate-blog-signup':
			if ( 'all' === $active_signup || 'blog' === $active_signup ) {
				validate_blog_signup();
			} else {
				_e( 'Site registration has been disabled.' );
			}
			break;
		case 'gimmeanotherblog':
			validate_another_blog_signup();
			break;
		case 'default':
		default :
			$user_email = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
			/**
			 * Fires when the site sign-up form is sent.
			 *
			 * @since 3.0.0
			 */
			do_action( 'preprocess_signup_form' );
			if ( is_user_logged_in() && ( 'all' === $active_signup || 'blog' === $active_signup ) ) {
				signup_another_blog( $newblogname );
			} else if ( false === is_user_logged_in() && ( 'all' === $active_signup || 'user' === $active_signup ) ) {
				signup_user( $newblogname, $user_email );
			} else if ( false === is_user_logged_in() && 'blog' === $active_signup ) {
				_e( 'Sorry, new registrations are not allowed at this time.' );
			} else {
				_e( 'You are logged in already. No need to register again!' );
			}

			if ( $newblogname ) {
				$newblog = get_blogaddress_by_name( $newblogname );

				if ( 'blog' === $active_signup || 'all' === $active_signup ) {
					printf( '<p><em>' . __( 'The site you were looking for, <strong>%s</strong>, does not exist, but you can create it now!' ) . '</em></p>', $newblog );
				} else {
					printf( '<p><em>' . __( 'The site you were looking for, <strong>%s</strong>, does not exist.' ) . '</em></p>', $newblog );
				}
			}
			break;
	}
}

/**
 * Fires after the sign-up forms, before wp_footer.
 *
 * @since 3.0.0
 */
do_action( 'after_signup_form' );

login_footer( 'user_login' );