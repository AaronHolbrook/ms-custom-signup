<?php

namespace MS_Custom_Signup;

/**
 * Generates and displays the Signup and Create Site forms
 *
 * @since MU
 *
 * @param string $blogname   The new site name
 * @param string $blog_title The new site title
 * @param array  $errors
 */
function show_blog_form( $blogname = '', $blog_title = '', $errors = '' ) {
	$current_site = get_current_site();

	// Blog name
	if ( ! is_subdomain_install() ) {
		printf( '<label for="blogname">%s</label>', esc_html__( 'Site Name:', 'ms-custom-signup' ) );
	} else {
		echo '<label for="blogname">' . esc_html__( 'Site Domain:' ) . '</label>';
	}

	if ( $errmsg = $errors->get_error_message( 'blogname' ) ) { ?>
		<p class="error">
			<?php echo esc_html( $errmsg ); ?>
		</p>
	<?php }

	if ( ! is_subdomain_install() ) {
		echo '<span class="prefix_address">' . $current_site->domain . $current_site->path . '</span><input name="blogname" type="text" id="blogname" value="' . esc_attr( $blogname ) . '" maxlength="60" /><br />';
	} else {
		echo '<input name="blogname" type="text" id="blogname" value="' . esc_attr( $blogname ) . '" maxlength="60" /><span class="suffix_address">.' . ( $site_domain = preg_replace( '|^www\.|', '', $current_site->domain ) ) . '</span><br />';
	}

	if ( ! is_user_logged_in() ) {
		if ( ! is_subdomain_install() ) {
			$site = $current_site->domain . $current_site->path . __( 'sitename' );
		} else {
			$site = esc_attr__( 'domain', 'ms-custom-signup' ) . '.' . $site_domain . $current_site->path;
		}
		echo '<p>(<strong>' . sprintf( __( 'Your address will be %s.' ), $site ) . '</strong>) ' . __( 'Must be at least 4 characters, letters and numbers only. It cannot be changed, so choose carefully!' ) . '</p>';
	}

	// Blog Title
	?>
	<label for="blog_title"><?php _e( 'Site Title:' ) ?></label>
	<?php if ( $errmsg = $errors->get_error_message( 'blog_title' ) ) { ?>
		<p class="error"><?php echo esc_html( $errmsg ); ?></p>
	<?php }
	echo '<input name="blog_title" type="text" id="blog_title" value="' . esc_attr( $blog_title ) . '" />';
	?>

	<div id="privacy">
		<p class="privacy-intro">
			<label for="blog_public_on"><?php _e( 'Privacy:' ) ?></label>
			<?php _e( 'Allow search engines to index this site.' ); ?>
			<br style="clear:both"/>
			<label class="checkbox" for="blog_public_on">
				<input type="radio" id="blog_public_on" name="blog_public" value="1"
				       <?php if ( ! isset( $_POST['blog_public'] ) || '1' == $_POST['blog_public'] ) { ?>checked="checked"<?php } ?> />
				<strong><?php _e( 'Yes' ); ?></strong>
			</label>
			<label class="checkbox" for="blog_public_off">
				<input type="radio" id="blog_public_off" name="blog_public" value="0"
				       <?php if ( isset( $_POST['blog_public'] ) && '0' == $_POST['blog_public'] ) { ?>checked="checked"<?php } ?> />
				<strong><?php _e( 'No' ); ?></strong>
			</label>
		</p>
	</div>

	<?php
	/**
	 * Fires after the site sign-up form.
	 *
	 * @since 3.0.0
	 *
	 * @param array $errors An array possibly containing 'blogname' or 'blog_title' errors.
	 */
	do_action( 'signup_blogform', $errors );
}

/**
 * Allow returning users to sign up for another site
 *
 * @since MU
 *
 * @param string $blogname   The new site name
 * @param string $blog_title The new blog title
 * @param array  $errors
 */
function signup_another_blog( $blogname = '', $blog_title = '', $errors = '' ) {
	$current_user = wp_get_current_user();

	if ( ! is_wp_error( $errors ) ) {
		$errors = new WP_Error();
	}

	$signup_defaults = array(
		'blogname'   => $blogname,
		'blog_title' => $blog_title,
		'errors'     => $errors,
	);

	/**
	 * Filter the default site sign-up variables.
	 *
	 * @since 3.0.0
	 *
	 * @param array $signup_defaults {
	 *                               An array of default site sign-up variables.
	 *
	 * @type string $blogname        The site blogname.
	 * @type string $blog_title      The site title.
	 * @type array  $errors          An array possibly containing 'blogname' or 'blog_title' errors.
	 * }
	 */
	$filtered_results = apply_filters( 'signup_another_blog_init', $signup_defaults );

	$blogname   = $filtered_results['blogname'];
	$blog_title = $filtered_results['blog_title'];
	$errors     = $filtered_results['errors'];

	echo '<h2>' . sprintf( __( 'Get <em>another</em> %s site in seconds' ), get_current_site()->site_name ) . '</h2>';

	if ( $errors->get_error_code() ) {
		echo '<p>' . esc_html__( 'There was a problem, please correct the form below and try again.' ) . '</p>';
	}
	?>
	<p><?php printf( __( 'Welcome back, %s. By filling out the form below, you can <strong>add another site to your account</strong>. There is no limit to the number of sites you can have, so create to your heart&#8217;s content, but write responsibly!' ), $current_user->display_name ) ?></p>

	<?php
	$blogs = get_blogs_of_user( $current_user->ID );
	if ( ! empty( $blogs ) ) { ?>

		<p><?php _e( 'Sites you are already a member of:' ) ?></p>
		<ul>
			<?php foreach ( $blogs as $blog ) {
				$home_url = get_home_url( $blog->userblog_id );
				echo '<li><a href="' . esc_url( $home_url ) . '">' . $home_url . '</a></li>';
			} ?>
		</ul>
	<?php } ?>

	<p><?php _e( 'If you&#8217;re not going to use a great site domain, leave it for a new user. Now have at it!' ) ?></p>
	<form id="setupform" method="post" action="wp-signup.php">
		<input type="hidden" name="stage" value="gimmeanotherblog"/>
		<?php
		/**
		 * Hidden sign-up form fields output when creating another site or user.
		 *
		 * @since MU
		 *
		 * @param string $context A string describing the steps of the sign-up process. The value can be
		 *                        'create-another-site', 'validate-user', or 'validate-site'.
		 */
		do_action( 'signup_hidden_fields', 'create-another-site' );
		?>
		<?php show_blog_form( $blogname, $blog_title, $errors ); ?>
		<p class="submit"><input type="submit" name="submit" class="submit"
		                         value="<?php esc_attr_e( 'Create Site' ) ?>"/></p>
	</form>
<?php
}

/**
 * Validate the new site signup
 *
 * @since MU
 *
 * @return array Contains the new site data and error messages.
 */
function validate_blog_form() {
	$user = '';
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
	}

	return wpmu_validate_blog_signup( $_POST['blogname'], $_POST['blog_title'], $user );
}

/**
 * Confirm a new site signup
 *
 * @since MU
 *
 * @param string $domain     The domain URL
 * @param string $path       The site root path
 * @param string $user_name  The username
 * @param string $user_email The user's email address
 * @param array  $meta       Any additional meta from the 'add_signup_meta' filter in validate_blog_signup()
 */
function confirm_another_blog_signup( $domain, $path, $blog_title, $user_name, $user_email = '', $meta = array() ) {
	?>
	<h2><?php printf( __( 'The site %s is yours.' ), "<a href='http://{$domain}{$path}'>{$blog_title}</a>" ) ?></h2>
	<p>
		<?php printf( __( '<a href="http://%1$s">http://%2$s</a> is your new site. <a href="%3$s">Log in</a> as &#8220;%4$s&#8221; using your existing password.' ), $domain . $path, $domain . $path, 'http://' . $domain . $path . 'wp-login.php', $user_name ) ?>
	</p>
	<?php
	/**
	 * Fires when the site or user sign-up process is complete.
	 *
	 * @since 3.0.0
	 */
	do_action( 'signup_finished' );
}

/**
 * Setup the new site signup
 *
 * @since MU
 *
 * @param string $user_name  The username
 * @param string $user_email The user's email address
 * @param string $blogname   The site name
 * @param string $blog_title The site title
 * @param array  $errors
 */
function signup_blog( $user_name = '', $user_email = '', $blogname = '', $blog_title = '', $errors = '' ) {
	if ( ! is_wp_error( $errors ) ) {
		$errors = new WP_Error();
	}

	$signup_blog_defaults = array(
		'user_name'  => $user_name,
		'user_email' => $user_email,
		'blogname'   => $blogname,
		'blog_title' => $blog_title,
		'errors'     => $errors
	);

	/**
	 * Filter the default site creation variables for the site sign-up form.
	 *
	 * @since 3.0.0
	 *
	 * @param array $signup_blog_defaults {
	 *                                    An array of default site creation variables.
	 *
	 * @type string $user_name            The user username.
	 * @type string $user_email           The user email address.
	 * @type string $blogname             The blogname.
	 * @type string $blog_title           The title of the site.
	 * @type array  $errors               An array of possible errors relevant to new site creation variables.
	 * }
	 */
	$filtered_results = apply_filters( 'signup_blog_init', $signup_blog_defaults );

	$user_name  = $filtered_results['user_name'];
	$user_email = $filtered_results['user_email'];
	$blogname   = $filtered_results['blogname'];
	$blog_title = $filtered_results['blog_title'];
	$errors     = $filtered_results['errors'];

	if ( empty( $blogname ) ) {
		$blogname = $user_name;
	}
	?>
	<form id="setupform" method="post" action="wp-signup.php">
		<input type="hidden" name="stage" value="validate-blog-signup"/>
		<input type="hidden" name="user_name" value="<?php echo esc_attr( $user_name ) ?>"/>
		<input type="hidden" name="user_email" value="<?php echo esc_attr( $user_email ) ?>"/>
		<?php
		/** This action is documented in wp-signup.php */
		do_action( 'signup_hidden_fields', 'validate-site' );
		?>
		<?php show_blog_form( $blogname, $blog_title, $errors ); ?>
		<p class="submit"><input type="submit" name="submit" class="submit"
		                         value="<?php esc_attr_e( 'Signup' ) ?>"/></p>
	</form>
<?php
}

/**
 * Validate new site signup
 *
 * @since MU
 *
 * @return bool True if the site signup was validated, false if error
 */
function validate_blog_signup() {
	// Re-validate user info.
	$user_result = wpmu_validate_user_signup( $_POST['user_name'], $_POST['user_email'] );
	$user_name   = $user_result['user_name'];
	$user_email  = $user_result['user_email'];
	$user_errors = $user_result['errors'];

	if ( $user_errors->get_error_code() ) {
		signup_user( $user_name, $user_email, $user_errors );

		return false;
	}

	$result     = wpmu_validate_blog_signup( $_POST['blogname'], $_POST['blog_title'] );
	$domain     = $result['domain'];
	$path       = $result['path'];
	$blogname   = $result['blogname'];
	$blog_title = $result['blog_title'];
	$errors     = $result['errors'];

	if ( $errors->get_error_code() ) {
		signup_blog( $user_name, $user_email, $blogname, $blog_title, $errors );

		return false;
	}

	$public      = (int) $_POST['blog_public'];
	$signup_meta = array( 'lang_id' => 1, 'public' => $public );

	/** This filter is documented in wp-signup.php */
	$meta = apply_filters( 'add_signup_meta', $signup_meta );

	wpmu_signup_blog( $domain, $path, $blog_title, $user_name, $user_email, $meta );
	confirm_blog_signup( $domain, $path, $blog_title, $user_name, $user_email, $meta );

	return true;
}

/**
 * New site signup confirmation
 *
 * @since MU
 *
 * @param string $domain     The domain URL
 * @param string $path       The site root path
 * @param string $blog_title The new site title
 * @param string $user_name  The user's username
 * @param string $user_email The user's email address
 * @param array  $meta       Any additional meta from the 'add_signup_meta' filter in validate_blog_signup()
 */
function confirm_blog_signup( $domain, $path, $blog_title, $user_name = '', $user_email = '', $meta = array() ) {
	?>
	<h2><?php printf( __( 'Congratulations! Your new site, %s, is almost ready.' ), "<a href='http://{$domain}{$path}'>{$blog_title}</a>" ) ?></h2>

	<p><?php _e( 'But, before you can start using your site, <strong>you must activate it</strong>.' ) ?></p>
	<p><?php printf( __( 'Check your inbox at <strong>%s</strong> and click the link given.' ), $user_email ) ?></p>
	<p><?php _e( 'If you do not activate your site within two days, you will have to sign up again.' ); ?></p>
	<h2><?php _e( 'Still waiting for your email?' ); ?></h2>
	<p>
		<?php _e( 'If you haven&#8217;t received your email yet, there are a number of things you can do:' ) ?>
		<ul id="noemail-tips">
			<li><p><strong><?php _e( 'Wait a little longer. Sometimes delivery of email can be delayed by processes outside of our control.' ) ?></strong></p></li>
			<li><p><?php _e( 'Check the junk or spam folder of your email client. Sometime emails wind up there by mistake.' ) ?></p></li>
			<li><?php printf( __( 'Have you entered your email correctly? You have entered %s, if it&#8217;s incorrect, you will not receive your email.' ), $user_email ) ?></li>
		</ul>
	</p>
	<?php
	/** This action is documented in wp-signup.php */
	do_action( 'signup_finished' );
}

/**
 * Validate a new blog signup
 *
 * @since MU
 *
 * @return null|boolean True if blog signup was validated, false if error.
 *                      The function halts all execution if the user is not logged in.
 */
function validate_another_blog_signup() {
	global $wpdb, $blogname, $blog_title, $errors, $domain, $path;
	$current_user = wp_get_current_user();
	if ( ! is_user_logged_in() ) {
		die();
	}

	$result = validate_blog_form();

	// Extracted values set/overwrite globals.
	$domain     = $result['domain'];
	$path       = $result['path'];
	$blogname   = $result['blogname'];
	$blog_title = $result['blog_title'];
	$errors     = $result['errors'];

	if ( $errors->get_error_code() ) {
		signup_another_blog( $blogname, $blog_title, $errors );

		return false;
	}

	$public = (int) $_POST['blog_public'];

	$blog_meta_defaults = array(
		'lang_id' => 1,
		'public'  => $public
	);

	/**
	 * Filter the new site meta variables.
	 *
	 * @since      MU
	 * @deprecated 3.0.0 Use the 'add_signup_meta' filter instead.
	 *
	 * @param array $blog_meta_defaults An array of default blog meta variables.
	 */
	$meta_defaults = apply_filters( 'signup_create_blog_meta', $blog_meta_defaults );
	/**
	 * Filter the new default site meta variables.
	 *
	 * @since 3.0.0
	 *
	 * @param array $meta        {
	 *                           An array of default site meta variables.
	 *
	 * @type int    $lang_id     The language ID.
	 * @type int    $blog_public Whether search engines should be discouraged from indexing the site. 1 for true, 0 for false.
	 * }
	 */
	$meta = apply_filters( 'add_signup_meta', $meta_defaults );

	wpmu_create_blog( $domain, $path, $blog_title, $current_user->ID, $meta, $wpdb->siteid );
	confirm_another_blog_signup( $domain, $path, $blog_title, $current_user->user_login, $current_user->user_email, $meta );

	return true;
}