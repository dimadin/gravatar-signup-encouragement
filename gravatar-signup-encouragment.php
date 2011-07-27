<?php
/*
Plugin Name: Gravatar Signup Encouragement
Plugin URI: http://blog.milandinic.com/wordpress/plugins/gravatar-signup-encouragement/
Description: Displays message to users without gravatar that they don't have one with link to Gravatar's sign-up page (e-mail included).
Version: 3.0-alpha-1
Author: Milan Dinić
Author URI: http://blog.milandinic.com/
Text Domain: gse_textdomain
Domain Path: translations
*/

/*
* If file opened directly, return 403 error
*/

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

/*
* URL of gravatar-check.php
*/
function gravatar_signup_encouragement_check_url() {
	$gse_grav_check_url = plugins_url( 'gravatar-check.php', __FILE__ );
	return $gse_grav_check_url;
}

/*
* Load textdomain for internationalization
*/

function gravatar_signup_encouragement_textdomain() {
	load_plugin_textdomain( 'gse_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/translations');
}
add_action('init', 'gravatar_signup_encouragement_textdomain');

/**
 * Load options from database
 *
 * @since 3.0
 *
 * @return array Array of options values.
 */
function gravatar_signup_encouragement_get_option() {
	$gse_options = get_option( 'gravatar_signup_encouragement_settings' );
	return $gse_options;
}

/*
* Return default message
*/
function gravatar_signup_encouragement_default_message() {
	/*
	* Load l10n functions on activation
	*/
	if(!function_exists('__')) {
		load_plugin_textdomain( 'gse_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/translations');
	}
	
	$message = sprintf(__("You do not appear to have a registered Gravatar. Therefore, the default avatar will be shown with your comments on this site.

If you would like your own Gravatar, click <a href='%s' target='_blank'>here</a> to create one (link opens in new tab/window).", "gse_textdomain"), 'URL');

	return $message;	
}

/**
 * Load GSE actions at init
 *
 * @since 3.0
 */
function gravatar_signup_encouragement_init() {
	/* Load options */
	$gse_options = gravatar_signup_encouragement_get_option();
	
	/* 
	 * Load modal if needed
	 * via http://themehybrid.com/support/topic/adding-jquery-ui-using-wp_enqueue_script-and-firing-onload-events
	 */
	if ( isset( $_REQUEST['gseaftercommentingmodal'] ) ) {
		add_action( 'template_redirect', 'show_gravatar_signup_encouragement_after_commenting_modal' );
	}
	
	/* Load custom script on profile page if needed */
	if ( $gse_options['show_profile'] ) {
		add_action( 'show_user_profile', 'show_gravatar_signup_encouragement_profile' );
	}
	
	/* Enqueue jQuery and load custom script on registration page if needed */
	if ( $gse_options['show_registration'] ) {
		add_action( 'login_head', 'gravatar_signup_encouragement_enqueing_registration' );
		add_action( 'login_head', 'wp_print_scripts', 11 );
		add_action( 'register_form', 'show_gravatar_signup_encouragement_registration' );
	}
	
	/* Enqueue jQuery on multisite signup page if needed */
	if ( $gse_options['show_ms_signup'] ) {
		add_action( 'template_redirect', 'gravatar_signup_encouragement_enqueing_ms_signup' );
		add_action( 'signup_extra_fields', 'show_gravatar_signup_encouragement_ms_signup' );
	}
	
	/* Show encouragement in admin bar if needed */
	if ( $gse_options['show_in_admin_bar'] ) {
		add_action( 'admin_bar_menu', 'gravatar_signup_encouragement_admin_bar', 1 ); // Load early before user's gravatar
	}
	
	/* Show encouragement in admin notices if needed */
	if ( $gse_options['show_in_admin_notices'] ) {
		add_action( 'admin_notices', 'show_gravatar_signup_encouragement_admin_notice' );
	}
	
	/* Show encouragement in bbPress reply form if needed */
	if ( $gse_options['show_bbpress'] ) {
		wp_enqueue_script( 'jquery' );
		add_action( 'wp_footer', 'gravatar_signup_encouragement_bbpress' );
	}
	
	/* Add action for handler of remover of notice after upgrade to version 2.0 */
	if ( $gse_options['notice_upgrade_1_to_2'] ) {
		add_action( 'admin_init', 'gravatar_signup_encouragement_notice_upgrade_1_to_2_handler' );
	}
}
add_action( 'init', 'gravatar_signup_encouragement_init' );

/**
 * Load GSE actions at template_redirect
 *
 * @since 3.0
 */
function gravatar_signup_encouragement_template_redirect() {
	/* Load options */
	$gse_options = gravatar_signup_encouragement_get_option();
	
}
add_action( 'template_redirect', 'gravatar_signup_encouragement_template_redirect' );

/**
 * Save default options to database
 *
 * @since 3.0
 */
function gravatar_signup_encouragement_add_default_options() {
	/* Setup variable */
	$gse_options = array();
	
	/*
	 * By default, show message to unregistered commenters
	 * and show below: comment field (for comments), “Profile” header (profile), e-mail address (registration & signup)
	 */
	$gse_options['show_comments_unreg'] = '1';
	$gse_options['below_comments_unreg'] = '#comment';
	$gse_options['below_comments_reg'] = '#comment';
	$gse_options['below_profile'] = 'h2';
	$gse_options['below_registration'] = '#user_email';
	if ( function_exists('is_multisite') && is_multisite() && is_main_site() ) {
		$gse_options['below_ms_signup'] = '#user_email';
	}
	
	/* Add version number */
	$gse_options['version'] = '2.0';
	
	/*
	 * Load plugin textdomain only for activation hook
	 * so that default message could be saved in database localized
	 */
	load_plugin_textdomain( 'gse_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/translations');
	
	/* Add default message */
	$gse_options['tip_text'] = gravatar_signup_encouragement_default_message();

	/* Save options to the database */
	add_option( 'gravatar_signup_encouragement_settings', $gse_options );
}

/**
 * Add default options on activation of plugin
 *
 * @since 1.0
 */
function gravatar_signup_encouragement_activate() {
	/* Load options */
	$gse_options = gravatar_signup_encouragement_get_option();

	/* If no options, add default */
	if ( ! $gse_options ) {
		gravatar_signup_encouragement_add_default_options();
	}
}
register_activation_hook( __FILE__, 'gravatar_signup_encouragement_activate' );

/**
 * Update or add default options.
 *
 * Runs on admin_init to see what should do.
 *
 * @since 2.0
 */
function gravatar_signup_encouragement_upgrade() {
	/* Load options */
	$gse_options = gravatar_signup_encouragement_get_option();

	/* If no options, add default */
	if ( ! $gse_options ) {
		gravatar_signup_encouragement_add_default_options();
	/* Else update to version 2.0 */
	} else {
		if ( ! $gse_options['version'] ) {
			/* Make array with names of options */
			$elements = array( 'below_comments_unreg', 'below_comments_unreg_custom', 'below_comments_reg', 'below_comments_reg_custom', 'below_profile', 'below_profile_custom', 'below_registration', 'below_registration_custom' );
			
			/* Split array into keys with names of options */
			foreach ( $elements as $element ) :
				/* Check if option exists */
				if ( $gse_options[$element] ) {
					/*
					 * Get position of # in option's value.
					 * If it isn't a first character,
					 * add it in front of value and update option.
					 */
					if ( strpos( $gse_options[$element], '#' ) !== 0 ) {
						$gse_options[$element] = '#' . $gse_options[$element];
						update_option( 'gravatar_signup_encouragement_settings', $gse_options );
					}
				}
			endforeach;
			
			/* Add new version and notice about upgrade	*/
			$gse_options['version'] = '2.0';
			$gse_options['notice_upgrade_1_to_2'] = true;
			update_option( 'gravatar_signup_encouragement_settings', $gse_options );
		}
	}
}

/*
* Fire upgrade function on admin_init
*/
function gravatar_signup_encouragement_action_admin_init() {
	$gse_options = gravatar_signup_encouragement_get_option();
  
	if (!$gse_options || !$gse_options['version']) {
		gravatar_signup_encouragement_upgrade();
	}
}
add_action( 'admin_init', 'gravatar_signup_encouragement_action_admin_init' );

/*
* Remove options on uninstallation of plugin
*/

function gravatar_signup_encouragement_uninstall() {
	delete_option('gravatar_signup_encouragement_settings');
}
register_uninstall_hook(__FILE__, 'gravatar_signup_encouragement_uninstall');

/**
 * Add action link(s) to plugins page
 * Thanks Dion Hulse -- http://dd32.id.au/wordpress-plugins/?configure-link
 * (taken from Adminize plugin)
 */
function gravatar_signup_encouragement_filter_plugin_actions($links, $file){
	static $this_plugin;

	if( !$this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ){
		$settings_link = '<a href="' . admin_url('options-discussion.php') . '#gravatar_signup_encouragement_form' . '">' . __('Settings', 'gse_textdomain') . '</a>';
		$donate_link = '<a href="http://blog.milandinic.com/donate/">' . __('Donate', 'gse_textdomain') . '</a>';
		$links = array_merge( array($settings_link, $donate_link), $links); // before other links
	}
	return $links;
}
add_filter( 'plugin_action_links', 'gravatar_signup_encouragement_filter_plugin_actions', 10, 2 );

/*
* Add contextual help
* Taken from Adminize plugin
* (not in function right now)
*/

function gravatar_signup_encouragement_contextual_help($help) {
	
	//keep the existing help copy
	echo $help;
	//add some new copy
	echo "<h5>" . __("Gravatar Signup Encouragement", "gse_textdomain") . "</h5>";
	echo "<p>" . __("<a href='http://blog.milandinic.com/wordpress/plugins/gravatar-signup-encouragement/' target='_blank'>Gravatar Signup Encouragement Settings Documentation</a>", "gse_textdomain") . "</p>";
	echo "<p>" . __('<a href="http://wordpress.org/tags/gravatar-signup-encouragement" target="_blank">Gravatar Signup Encouragement Support Forums</a>', 'gse_textdomain') . "</p>";
}

/* 
* Load contextual help
* via http://forum.milo317.com/topic/hacks/page/30#post-1531
*/
function add_gravatar_signup_encouragement_contextual_help() {
	//the contextual help filter
	add_filter('contextual_help','gravatar_signup_encouragement_contextual_help');
}
add_action('load-options-discussion.php','add_gravatar_signup_encouragement_contextual_help');

/*
* Enqueue jQuery on singular page with opened comments
*/

function gravatar_signup_encouragement_enqueing_comments() {
	$gse_options = gravatar_signup_encouragement_get_option();
	if (is_singular() && comments_open()) {
		if ( (!is_user_logged_in() && $gse_options['show_comments_unreg']) || (is_user_logged_in() && $gse_options['show_comments_reg']) ) {
			wp_enqueue_script('jquery');
		}
	}
}
add_action('template_redirect', 'gravatar_signup_encouragement_enqueing_comments');

/*
* URL of screenshot
*/
function gravatar_signup_encouragement_screenshot_url($screenshot) {
	$gse_screenshot_url = plugins_url( $screenshot, __FILE__ );
	return $gse_screenshot_url;
}

/*
* Function to add fields on Discussion Settings page, section Gravatar
* based on http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
*/

function add_gravatar_signup_encouragement_settings_field() {
 
	/*
	* The fields are:
	* the id the form field will use
	* name to display on the page
	* callback function
	* the name of the page
	* the section of the page to add the field to
	*/
	/*add_settings_field('gravatar_signup_encouragement_settings' , __('Show Gravatar Signup Tip Below', 'gse_textdomain') ,
			'gravatar_signup_encouragement_field_showing' , 'discussion' , 'avatars');*/
	add_settings_field('gravatar_signup_encouragement_settings' , __('Gravatar Signup Encouragement', 'gse_textdomain') ,
			'gravatar_signup_encouragement_field_settings_form' , 'discussion' , 'avatars');
 
	//register the setting to make sure it gets checked
	register_setting('discussion','gravatar_signup_encouragement_settings');
}

/*
* Function for printing fields on Discussion Settings page
* based on http://planetozh.com/blog/2009/05/handling-plugins-options-in-wordpress-28-with-register_setting/
*/

function gravatar_signup_encouragement_field_settings_form() {
	global $wp_version;
	$gse_options = gravatar_signup_encouragement_get_option();
	
	/* Include options version if available */
	if ( $gse_options['version'] ) {
		?>
		<input type="hidden" name="gravatar_signup_encouragement_settings[version]" value="<?php echo $gse_options['version']; ?>" />
		<?php
	}
	
	// First we print selection of cases when to show tip ?>
	<span id="gravatar_signup_encouragement_form"><?php _e( 'Choose where to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
	<br />
	
	<?php // Comments for unregistered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_comments_unreg]" class="gse_show_comments_unreg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_comments_unreg']); ?> /> <?php _e( 'Comment form (unregistered users)', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-2.jpg' ); ?>" title="<?php _e( 'Message shown below comment text field on a Twenty Ten theme', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_comments_unreg" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose the comment form element or text field to display the Gravatar Signup Encouragement message below it', 'gse_textdomain' ); ?></span>
			<br />
			<?php
			//If theme doesn't follow standards, add notice
			if ( gravatar_signup_encouragement_is_theme_in_list() ) {
				?><div class="dashboard-widget-notice">
				<?php
				echo sprintf(__("Notice: Theme you are using doesn't fully follow WordPress theme standards so some of predefined fields in comment form might not work. You can add custom element to make it work. Read <a href='%s' class='thickbox'>here</a> for more information.", "gse_textdomain" ), "#TB_inline?height=500&width=400&inlineId=gsenonstandardthemetips&modal=true");
				?>
				</div>
			<?php
			}
			?>
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="#comment" 
			<?php checked('#comment', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'Comment text', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="#url" 
			<?php checked('#url', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'URL', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="#email" 
			<?php checked('#email', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="#submit" 
			<?php checked('#submit', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" class="gse_below_comments_unreg_custom_radio" type="radio" value="<?php echo $gse_options['below_comments_unreg_custom']; ?>" 
			<?php checked($gse_options['below_comments_unreg_custom'], $gse_options['below_comments_unreg']); ?> /> <?php _e( 'Custom element:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_comments_unreg_custom]" type="text" class="gse_below_comments_unreg_custom_text" value="<?php echo $gse_options['below_comments_unreg_custom']; ?>" /> <?php _e( 'Use <a href="http://api.jquery.com/category/selectors/" target="_blank">jQuery selectors</a> to choose any element on a page', 'gse_textdomain' ); ?>
		</div>
	<br />
	
	<?php // Comments for registered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_comments_reg]" class="gse_show_comments_reg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_comments_reg']); ?> /> <?php _e( 'Comment form (registered users)', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-3.jpg' ); ?>" title="<?php _e( 'Message shown below comment text field on a Twenty Ten theme', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_comments_reg" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose the comment form element or text field to display the Gravatar Signup Encouragement message below it', 'gse_textdomain' ); ?></span>
			<br />
			<?php
			//If theme doesn't follow standards, add notice
			if ( gravatar_signup_encouragement_is_theme_in_list() ) {
				?><div class="dashboard-widget-notice">
				<?php
				echo sprintf(__("Notice: Theme you are using doesn't fully follow WordPress theme standards so some of predefined fields in comment form might not work. You can add custom element to make it work. Read <a href='%s' class='thickbox'>here</a> for more information.", "gse_textdomain" ), "#TB_inline?height=500&width=400&inlineId=gsenonstandardthemetips&modal=true");
				?>
				</div>
			<?php
			}
			?>
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" type="radio" value="#comment" 
			<?php checked('#comment', $gse_options['below_comments_reg']); ?> /> <?php _e( 'Comment text', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" type="radio" value="#commentform p:first" 
			<?php checked('#commentform p:first', $gse_options['below_comments_reg']); ?> /> <?php _e( 'Logout URL', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" type="radio" value="#submit" 
			<?php checked('#submit', $gse_options['below_comments_reg']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" class="gse_below_comments_reg_custom_radio" type="radio" value="<?php echo $gse_options['below_comments_reg_custom']; ?>" 
			<?php checked($gse_options['below_comments_reg_custom'], $gse_options['below_comments_reg']); ?> /> <?php _e( 'Custom element:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_comments_reg_custom]" type="text" class="gse_below_comments_reg_custom_text" value="<?php echo $gse_options['below_comments_reg_custom']; ?>" /> <?php _e( 'Use <a href="http://api.jquery.com/category/selectors/" target="_blank">jQuery selectors</a> to choose any element on a page', 'gse_textdomain' ); ?>
		</div>
	<br />
	
	<?php // Modal for unregistered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_after_commenting_modal_unreg]" class="gse_show_after_commenting_modal_unreg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_after_commenting_modal_unreg']); ?> /> <?php _e( 'Dialog after comment posting (unregistered users)', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-4.jpg' ); ?>" title="<?php _e( 'Message shown in a dialog over a Twenty Ten theme after comment is posted', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
	<br />
	
	<?php // Modal for registered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_after_commenting_modal_reg]" class="gse_show_after_commenting_modal_reg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_after_commenting_modal_reg']); ?> /> <?php _e( 'Dialog after comment posting (registered users)', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-5.jpg' ); ?>" title="<?php _e( 'Message shown in a dialog over a Twenty Ten theme after comment is posted', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
	<br />
	
	<?php // Admin bar ?>
	<?php if ( version_compare( $wp_version, '3.1', '>=' ) ) { ?>
	<label><input name="gravatar_signup_encouragement_settings[show_in_admin_bar]" class="gse_show_in_admin_bar" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_in_admin_bar']); ?> /> <?php _e( 'Admin bar', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-6.jpg' ); ?>" title="<?php _e( 'Message shown in admin bar', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
	<br />
	<?php } ?>
	
	<?php // Admin notice ?>
	<label><input name="gravatar_signup_encouragement_settings[show_in_admin_notices]" class="gse_show_in_admin_notices" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_in_admin_notices']); ?> /> <?php _e( 'Administration notice', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-6.jpg' ); ?>" title="<?php _e( 'Message shown in administration notices', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
	<br />
	
	<?php // Profile ?>
	<label><input name="gravatar_signup_encouragement_settings[show_profile]" class="gse_show_profile" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_profile']); ?> /> <?php _e( 'Profile page', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-7.jpg' ); ?>" title="<?php _e( 'Message shown on a profile page below “Profile” header', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_profile" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose the profile page form element or text field to display the Gravatar Signup Encouragement message below it', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="h2" 
			<?php checked('h2', $gse_options['below_profile']); ?> /> <?php _e( 'Header &#8220;Profile&#8221;', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="#your-profile h3:eq(1)" 
			<?php checked('#your-profile h3:eq(1)', $gse_options['below_profile']); ?> /> <?php _e( 'Header &#8220;Name&#8221;', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="#user_login + .description" 
			<?php checked('#user_login + .description', $gse_options['below_profile']); ?> /> <?php _e( 'User name', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="#display_name" 
			<?php checked('#display_name', $gse_options['below_profile']); ?> /> <?php _e( 'Nicename', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="#email" 
			<?php checked('#email', $gse_options['below_profile']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="#your-profile h3:eq(3)" 
			<?php checked('#your-profile h3:eq(3)', $gse_options['below_profile']); ?> /> <?php _e( 'Header &#8220;About Yourself&#8221;', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="#description + br + .description" 
			<?php checked('#description + br + .description', $gse_options['below_profile']); ?> /> <?php _e( 'Biographical Info', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value=".form-table:last" 
			<?php checked('.form-table:last', $gse_options['below_profile']); ?> /> <?php _e( 'Last input field (by default, &#8220;New Password&#8221;)', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" class="gse_below_profile_custom_radio" type="radio" value="<?php echo $gse_options['below_profile_custom']; ?>" 
			<?php checked($gse_options['below_profile_custom'], $gse_options['below_profile']); ?> /> <?php _e( 'Custom element:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_profile_custom]" type="text" class="gse_below_profile_custom_text" value="<?php echo $gse_options['below_profile_custom']; ?>" /> <?php _e( 'Use <a href="http://api.jquery.com/category/selectors/" target="_blank">jQuery selectors</a> to choose any element on a page', 'gse_textdomain' ); ?>
		</div>
	<br />
	
	<?php // Registration ?>
	<label><input name="gravatar_signup_encouragement_settings[show_registration]" class="gse_show_registration" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_registration']); ?> /> <?php _e( 'Registration page', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-8.jpg' ); ?>" title="<?php _e( 'Message shown on a registration page below e-mail address text field', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_registration" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose the registration page form element or text field to display the Gravatar Signup Encouragement message below it', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" type="radio" value="#user_email" 
			<?php checked('#user_email', $gse_options['below_registration']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" type="radio" value="#user_login" 
			<?php checked('#user_login', $gse_options['below_registration']); ?> /> <?php _e( 'User name', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" type="radio" value="#wp-submit" 
			<?php checked('#wp-submit', $gse_options['below_registration']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" class="gse_below_registration_custom_radio" type="radio" value="<?php echo $gse_options['below_registration_custom']; ?>" 
			<?php checked($gse_options['below_registration_custom'], $gse_options['below_registration']); ?> /> <?php _e( 'Custom element:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_registration_custom]" type="text" class="gse_below_registration_custom_text" value="<?php echo $gse_options['below_registration_custom']; ?>" /> <?php _e( 'Use <a href="http://api.jquery.com/category/selectors/" target="_blank">jQuery selectors</a> to choose any element on a page', 'gse_textdomain' ); ?>
		</div>
	<br />
	
	<?php // Sign-up (multisite) ?>
	<?php if ( function_exists('is_multisite') && is_multisite() && is_main_site() && is_super_admin() ) { ?>
	<label><input name="gravatar_signup_encouragement_settings[show_ms_signup]" class="gse_show_ms_signup" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_ms_signup']); ?> /> <?php _e( 'Signup page (multisite)', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-9.jpg' ); ?>" title="<?php _e( 'Message shown on a registration page (multisite) on a Twenty Ten theme below e-mail address text field', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_ms_signup" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose the (multisite) registration page form element or text field to display the Gravatar Signup Encouragement message below it', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_ms_signup]" type="radio" value="#user_email" 
			<?php checked('#user_email', $gse_options['below_ms_signup']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_ms_signup]" type="radio" value="#user_name" 
			<?php checked('#user_name', $gse_options['below_ms_signup']); ?> /> <?php _e( 'User name', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_ms_signup]" type="radio" value="#submit" 
			<?php checked('#submit', $gse_options['below_ms_signup']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_ms_signup]" class="gse_below_ms_signup_custom_radio" type="radio" value="<?php echo $gse_options['below_ms_signup_custom']; ?>" 
			<?php checked($gse_options['below_ms_signup_custom'], $gse_options['below_ms_signup']); ?> /> <?php _e( 'Custom ID:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_ms_signup_custom]" type="text" class="gse_below_ms_signup_custom_text" value="<?php echo $gse_options['below_ms_signup_custom']; ?>" /> 
		</div>
	<br />
	<?php } ?>
	
	<?php // bbPress ?>
	<?php if ( class_exists( 'bbPress' ) ) { ?>
	<label><input name="gravatar_signup_encouragement_settings[show_bbpress]" class="gse_show_bbpress" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_bbpress']); ?> /> <?php _e( 'bbPress reply form', 'gse_textdomain' ); ?> </label> (<a href="<?php echo gravatar_signup_encouragement_screenshot_url( 'screenshot-9.jpg' ); ?>" title="<?php _e( 'Message shown on a registration page (multisite) on a Twenty Ten theme below e-mail address text field', 'gse_textdomain' ); ?>" class="thickbox"><?php _e( 'example of how this looks', 'gse_textdomain' ); ?></a>)
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_bbpress" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose the bbPress reply form element or text field to display the Gravatar Signup Encouragement message below it', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_bbpress]" type="radio" value="#bbp_topic_tags" 
			<?php checked('#bbp_topic_tags', $gse_options['below_bbpress']); ?> /> <?php _e( 'Topic tags', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_bbpress]" type="radio" value="#new-post div.avatar" 
			<?php checked('#new-post div.avatar', $gse_options['below_bbpress']); ?> /> <?php _e( 'Avatar', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_bbpress]" type="radio" value="#new-post .bbp-form p:last" 
			<?php checked('#new-post .bbp-form p:last', $gse_options['below_bbpress']); ?> /> <?php _e( 'Last input field', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_bbpress]" type="radio" value="#new-post button" 
			<?php checked('#new-post button', $gse_options['below_bbpress']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_bbpress]" class="gse_below_bbpress_custom_radio" type="radio" value="<?php echo $gse_options['below_bbpress_custom']; ?>" 
			<?php checked($gse_options['below_bbpress_custom'], $gse_options['below_bbpress']); ?> /> <?php _e( 'Custom ID:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_bbpress_custom]" type="text" class="gse_below_bbpress_custom_text" value="<?php echo $gse_options['below_bbpress_custom']; ?>" /> 
		</div>
	<br />
	<?php } ?>
	
	
	<br /><br />
	<?php
	/*
	* Show upgrade notice
	*/
	if ( $gse_options['notice_upgrade_1_to_2'] ) {
		?><div class="dashboard-widget-notice">
		<?php _e( "There are new options for Gravatar Signup Encouragement.", "gse_textdomain" );?><br />
		<ol>
			<li><?php _e( "Now you can show message in dialog after comment is posted, as an administration notice, and on a signup page for multisite installation.", "gse_textdomain" );?></li>
			<li><?php _e( "Now you can add message after any element on a page more easily then before.", "gse_textdomain" );?></li>
			<li><?php _e( "There are new predefined elements for profile page.", "gse_textdomain" );?></li>
			<li><?php _e( "Finally, there is a new, longer, and more descriptive default message. You can use the new default message, your existing message, or update your existing message.", "gse_textdomain" );?></li>
		</ol>
		<?php _e( "New default message:", "gse_textdomain" );?><br />
		<textarea  readonly="true" rows="5" cols="50" class="large-text code"><?php echo gravatar_signup_encouragement_default_message(); ?></textarea><br />
		<?php printf( '<a href="%s" id="gse-notice-1-to-2-no">' . __('Do not show this notice again', 'gse_textdomain') . '</a>', '?gse_notice_1_to_2=0' ); ?></div><br />
	<?php } ?>
	
	<?php
	/*
	* Show notice if no message
	*/
	if ( !$gse_options['tip_text'] ) {
		?><div class="dashboard-widget-notice">
		<?php _e( "You have not created a custom message. Below is the default message, to help with creating your own custom message.", "gse_textdomain" );?><br />
		<?php _e( "Default message:", "gse_textdomain" );?><br />
		<textarea  readonly="true" rows="5" cols="50" class="large-text code"><?php echo gravatar_signup_encouragement_default_message(); ?></textarea><br />
		</div>
	<?php } ?>
	
	<?php _e( "Message to display to users who have not registered a Gravatar.", 'gse_textdomain' ); ?><br />
	<?php _e( 'You should leave <strong>URL</strong> since it is automatically replaced with appropriate link to signup page on gravatar.com.', 'gse_textdomain' ); ?><br />
	<?php _e( 'Do not use double quotes (<strong>"</strong>) since it will break code. Instead, use curly quotes (<strong>&#8220;</strong> and <strong>&#8221;</strong>) for text, and single quotes (<strong>&#039;</strong>) for HTML tags.', 'gse_textdomain' ); ?><br />
	<label><textarea name="gravatar_signup_encouragement_settings[tip_text]" rows="5" cols="50" id="gravatar_signup_encouragement_settings[tip_text]" class="large-text code"><?php echo $gse_options['tip_text']; ?></textarea></label>
	
	<?php
	/* Show encouragement to install Use Google Libraries plugin if it's not installed */
	if ( ! class_exists( 'JCP_UseGoogleLibraries' ) ) {
		?>
		<div class="dashboard-widget-notice">
			<strong><?php _e( "Notice:", "gse_textdomain" ); ?></strong><br />
			<?php _e( "Plugin Gravatar Signup Encouragement uses jQuery for display of messages. You can speed up your site by installing plugin <a href='http://jasonpenney.net/wordpress-plugins/use-google-libraries/'>Use Google Libraries</a>, which will load jQuery from Google's CDN.", "gse_textdomain" );
			echo sprintf( __( " (<a href='%s'>read here for more information</a>)", "gse_textdomain" ), "http://encosia.com/2008/12/10/3-reasons-why-you-should-let-google-host-jquery-for-you/" ); ?><br />
			<?php echo sprintf( __( "<a href='%s' class='thickbox'>Install Use Google Libraries</a>", "gse_textdomain" ),  esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=use-google-libraries&TB_iframe=true&width=600&height=550' ) ) ); ?><br />
		</div>
		<?php
	}
	
	/* Show donation encouragement */
	?>
	<div class="dashboard-widget-notice">
		<br />
		<strong>
		<?php
		echo sprintf( __( "If you like plugin Gravatar Signup Encouragement, you can <a href='%s'>donate</a> to the author to support its further development.", "gse_textdomain" ), "http://blog.milandinic.com/donate/" );
		?>
		</strong><br />
	</div>
	<?php
	
	// Last we print jQuery script for show/hide on checkbox and text-to-radio input value ?>
<script language="javascript">
jQuery(document).ready(function()
{	
	<?php
	/*
	* Check if case is turned on; if no, hide it
	* If checkbox is checked, show; if unchecked, hide
	*/
	?>
	<?php if( !$gse_options['show_comments_unreg'] ){ ?>
	jQuery('#gse_below_comments_unreg').hide();
	<?php } ?>
	jQuery('.gse_show_comments_unreg').change(function() {
		if(jQuery('.gse_show_comments_unreg').attr('checked'))
			jQuery('#gse_below_comments_unreg').show();
		else
			jQuery('#gse_below_comments_unreg').hide();
		return true;
	});
	
	<?php if( !$gse_options['show_comments_reg'] ){ ?>
	jQuery('#gse_below_comments_reg').hide();
	<?php } ?>
	jQuery('.gse_show_comments_reg').change(function() {
		if(jQuery('.gse_show_comments_reg').attr('checked'))
			jQuery('#gse_below_comments_reg').show();
		else
			jQuery('#gse_below_comments_reg').hide();
		return true;
	});
	
	<?php if( !$gse_options['show_profile'] ){ ?>
	jQuery('#gse_below_profile').hide();
	<?php } ?>
	jQuery('.gse_show_profile').change(function() {
		if(jQuery('.gse_show_profile').attr('checked'))
			jQuery('#gse_below_profile').show();
		else
			jQuery('#gse_below_profile').hide();
		return true;
	});
	
	<?php if( !$gse_options['show_registration'] ){ ?>
	jQuery('#gse_below_registration').hide();
	<?php } ?>
	jQuery('.gse_show_registration').change(function() {
		if(jQuery('.gse_show_registration').attr('checked'))
			jQuery('#gse_below_registration').show();
		else
			jQuery('#gse_below_registration').hide();
		return true;
	});
	
	<?php if( !$gse_options['show_ms_signup'] ){ ?>
	jQuery('#gse_below_ms_signup').hide();
	<?php } ?>
	jQuery('.gse_show_ms_signup').change(function() {
		if(jQuery('.gse_show_ms_signup').attr('checked'))
			jQuery('#gse_below_ms_signup').show();
		else
			jQuery('#gse_below_ms_signup').hide();
		return true;
	});
	
	<?php if( !$gse_options['show_bbpress'] ){ ?>
	jQuery('#gse_below_bbpress').hide();
	<?php } ?>
	jQuery('.gse_show_bbpress').change(function() {
		if(jQuery('.gse_show_bbpress').attr('checked'))
			jQuery('#gse_below_bbpress').show();
		else
			jQuery('#gse_below_bbpress').hide();
		return true;
	});
	
	<?php 
	/*
	* Get value from text input field of custom element on keyup
	* and place it in radio button value
	*/
	?>
	jQuery('.gse_below_comments_unreg_custom_text').keyup(function(event){
		var gse_below_comments_unreg_custom = jQuery('.gse_below_comments_unreg_custom_text').val();
		jQuery('.gse_below_comments_unreg_custom_radio').val(gse_below_comments_unreg_custom);
	});
	
	jQuery('.gse_below_comments_reg_custom_text').keyup(function(event){
		var gse_below_comments_reg_custom = jQuery('.gse_below_comments_reg_custom_text').val();
		jQuery('.gse_below_comments_reg_custom_radio').val(gse_below_comments_reg_custom);
	});
	
	jQuery('.gse_below_profile_custom_text').keyup(function(event){
		var gse_below_profile_custom = jQuery('.gse_below_profile_custom_text').val();
		jQuery('.gse_below_profile_custom_radio').val(gse_below_profile_custom);
	});
	
	jQuery('.gse_below_registration_custom_text').keyup(function(event){
		var gse_below_registration_custom = jQuery('.gse_below_registration_custom_text').val();
		jQuery('.gse_below_registration_custom_radio').val(gse_below_registration_custom);
	});
	
	jQuery('.gse_below_ms_signup_custom_text').keyup(function(event){
		var gse_below_ms_signup_custom = jQuery('.gse_below_ms_signup_custom_text').val();
		jQuery('.gse_below_ms_signup_custom_radio').val(gse_below_ms_signup_custom);
	});
	
	jQuery('.gse_below_bbpress_custom_text').keyup(function(event){
		var gse_below_bbpress_custom = jQuery('.gse_below_bbpress_custom_text').val();
		jQuery('.gse_below_bbpress_custom_radio').val(gse_below_bbpress_custom);
	});
});
</script>

	<?php // Last we print Thickboxes for bad themes ?>
	<?php
	if ( gravatar_signup_encouragement_is_theme_in_list() ) {
		?>
		<div id="gsenonstandardthemetips" style="display:none">
			<h3 style="text-align: center;"><?php _e( 'Nonstandard default elements on your theme', 'gse_textdomain' ); ?></h3>
			<p id="gse_nonstandard_theme_tips_text">
				<?php _e( 'Some default elements will not work on your theme and they are listed below. You need to add value of element to field <em>Custom element</em>. List below contains element name and value:', 'gse_textdomain' );
				if ( get_stylesheet() == 'mystique' ) {
				?>
				<ul>
					<li><strong><?php _e( 'Comment form (unregistered users)', 'gse_textdomain' ); ?></strong>
						<ul>
							<li><?php _e( 'URL', 'gse_textdomain' ); ?> <input type="text" value="input[name='url']" size="35" /></li>
							<li><?php _e( 'E-mail address', 'gse_textdomain' ); ?> <input type="text" value="input[name='email']" size="35" /></li>
						</ul>
					</li>
				</ul>
				<?php
				} elseif ( get_stylesheet() == 'carrington-blog' ) {
				?>
				<ul>
					<li><strong><?php _e( 'Comment form (unregistered users)', 'gse_textdomain' ); ?></strong>
						<ul>
							<li><?php _e( 'Comment text', 'gse_textdomain' ); ?> <input type="text" value="textarea[name='comment']" size="35" /></li>
							<li><?php _e( 'URL', 'gse_textdomain' ); ?> <input type="text" value="input[name='url']" size="35" /></li>
							<li><?php _e( 'E-mail address', 'gse_textdomain' ); ?> <input type="text" value="input[name='email']" size="35" /></li>
							<li><?php _e( 'Submit button', 'gse_textdomain' ); ?> <input type="text" value="input[name='submit']" size="35" /></li>
						</ul>
					</li>
					<li><strong><?php _e( 'Comment form (registered users)', 'gse_textdomain' ); ?></strong>
						<ul>
							<li><?php _e( 'Comment text', 'gse_textdomain' ); ?> <input type="text" value="textarea[name='comment']" size="35" /></li>
							<li><?php _e( 'Logout URL', 'gse_textdomain' ); ?> <input type="text" value=".logged-in" size="35" /></li>
							<li><?php _e( 'Submit button', 'gse_textdomain' ); ?> <input type="text" value="input[name='submit']" size="35" /></li>
						</ul>
					</li>
				</ul>
				<?php
				}
				?>
			</p>
			<p style="text-align:center" id="gse_nonstandard_theme_tips_buttons">
				<input type="submit" id="gse_nonstandard_theme_tips_close_button" value="<?php _e( 'Close this message', 'gse_textdomain' ); ?>" onclick="tb_remove()" />
			</p> 
		</div>
	<?php
	}
}

//add action so that fields are actually shown
add_action('admin_init', 'add_gravatar_signup_encouragement_settings_field');

/**
 * Check if gravatar exists
 */
function gravatar_signup_encouragement_check_gravatar_existence($email) {
	$fileUrl = "http://www.gravatar.com/avatar/".md5( strtolower($email) )."?s=2&d=404";
	$AgetHeaders = @get_headers($fileUrl);
	if (!preg_match("|200|", $AgetHeaders[0])) {
		return false;
	} else {
		return true;
	}
}

/**
 * Locale gravatar signup URL
 */
function gravatar_signup_encouragement_locale_signup_url( $email = '' ) {
	/* translators: Locale gravatar.com, e.g. sr.gravatar.com for Serbian */
	$gse_locale_url = _x('en.gravatar.com', 'Locale gravatar.com, e.g. sr.gravatar.com for Serbian', 'gse_textdomain');
	
	// check if it is really locale.gravatar.com
	if (preg_match('|^[A-Z_%+-]+.gravatar+.com|i', $gse_locale_url)) {
		$gse_locale_url = $gse_locale_url; } else {
		$gse_locale_url = 'en.gravatar.com';
	}
	
	if ( empty($email) ) {
		$gse_url = "http://" . $gse_locale_url . '/site/signup/';
	} else {
		$gse_url = "http://" . $gse_locale_url . '/site/signup/' . $email;
	}
	
	return $gse_url;
}

/*
* Prepeare message for use
*/

function gravatar_signup_encouragement_message( $email = '', $onclick = '') {
	$gse_options = gravatar_signup_encouragement_get_option();
	
	/*
	* Localize URL
	* and format it for use
	*/	
	if ( empty($email) ) {
		$gse_url = gravatar_signup_encouragement_locale_signup_url() . '" + emailValue + "';
	} else {
		$gse_url = gravatar_signup_encouragement_locale_signup_url($email);
	}
	
	/*
	* If there is no message, use default
	*/
	if (!$gse_options['tip_text']) {
		$gse_options['tip_text'] = gravatar_signup_encouragement_default_message();
	}
	
	/*
	* Replace placeholder with real URL
	*/
	$gse_tip_text = preg_replace('/URL/', $gse_url, $gse_options['tip_text']);
	
	/*
	* Add onclick
	*/
	if ( !empty($onclick) ) {
		$gse_tip_text = preg_replace('/a href/', 'a onclick="' . $onclick . '" href', $gse_tip_text);
	}
	
	/*
	* Replace new lines with <br />
	* Code from http://www.projectpier.org/node/771
	* via http://www.learningjquery.com/2006/11/really-simple-live-comment-preview
	*/
	$gse_tip_text = str_replace("\r", "", $gse_tip_text);  // Remove \r
	$gse_tip_text = str_replace("\n", "<br />", $gse_tip_text);  // Replace \n with <br />
	/*
	* Print message
	*/
	
	return $gse_tip_text;
}

/*
* Add encouragement on comment form for unregistered users
*/
		
function show_gravatar_signup_encouragement_com_unreg() {
	$gse_options = gravatar_signup_encouragement_get_option();
	
	/*
	* Show message if user commented before
	*/
	$gse_current_commenter = wp_get_current_commenter();
	if ( $gse_current_commenter['comment_author_email'] ) {
		?>
	
<script language="javascript">
jQuery(document).ready(function()
{
	<?php // post and check if gravatar exists or not from ajax ?>
	var emailValue = jQuery("<?php echo apply_filters('gse_get_email_value_com_unreg', '#email'); ?>").val();
	jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:emailValue } ,function(data)
	{
	  if(data) <?php // if gravatar doesn't exist ?>
	  {
		var emailValue = jQuery("<?php echo apply_filters('gse_get_email_value_com_unreg', '#email'); ?>").val(); <?php // pick up e-mail address from field ?>
		
		jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>

		jQuery("<?php echo $gse_options['below_comments_unreg']; ?>").after("<br /><div id='gse_comments_message'><?php echo apply_filters('gse_message_com_unreg', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
	  }  	
	  else
	  {
		jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>
	  }
	});
});
</script>
	<?php
	}
	?>
	
<script language="javascript">
jQuery(document).ready(function()
{
	jQuery("<?php echo apply_filters('gse_get_email_value_com_unreg', '#email'); ?>").blur(function() <?php // when user leave #email field ?>
	{		
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:jQuery(this).val() } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = jQuery("<?php echo apply_filters('gse_get_email_value_com_unreg', '#email'); ?>").val(); <?php // pick up e-mail address from field ?>
			
			jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("<?php echo $gse_options['below_comments_unreg']; ?>").after("<br /><div id='gse_comments_message'><?php echo apply_filters('gse_message_com_unreg', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
          }  	
		  else
		  {
			jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>
		  }
        });
 
	});
});
</script>
	<?php
}

/*
* Add encouragement on comment form for registered users
*/
		
function show_gravatar_signup_encouragement_com_reg() {
	global $user_email;
	$gse_options = gravatar_signup_encouragement_get_option();
	?>
	
<script language="javascript">
jQuery(document).ready(function()
{		
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:"<?php echo $user_email; ?>" } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = "<?php echo $user_email; ?>"; <?php // pick up e-mail address from wp_usermeta ?>
			
			jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("<?php echo $gse_options['below_comments_reg']; ?>").after("<br /><div id='gse_comments_message'><?php echo apply_filters('gse_message_com_reg', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
          }  				
        });
});
</script>
	<?php
}

/*
* Actions to print jQuery code for comment form
*/
function gravatar_signup_encouragement_comment_form() {
	$gse_options = gravatar_signup_encouragement_get_option();
	if ( !is_user_logged_in() && $gse_options['show_comments_unreg'] ) {
		add_action('comment_form', 'show_gravatar_signup_encouragement_com_unreg');
	}
	elseif ( is_user_logged_in() && $gse_options['show_comments_reg'] ) {
		add_action('comment_form', 'show_gravatar_signup_encouragement_com_reg');
	}
}
add_action('template_redirect', 'gravatar_signup_encouragement_comment_form');

/*
* Add encouragement modal after comment posting
*/
function show_gravatar_signup_encouragement_after_commenting_modal() {
	$gse_options = gravatar_signup_encouragement_get_option();
	if ( ( !is_user_logged_in() && $gse_options['show_after_commenting_modal_unreg'] ) || ( is_user_logged_in() && $gse_options['show_after_commenting_modal_reg'] ) ) {
		function gravatar_signup_encouragement_load_thickbox() {
			add_thickbox();
			?>
				<script type="text/javascript">
					/* <![CDATA[ */
					var tb_pathToImage = '<?php echo esc_js( includes_url( '/js/thickbox/loadingAnimation.gif' ) ); ?>';
					var tb_closeImage = '<?php echo esc_js( includes_url( '/js/thickbox/tb-close.png' ) ); ?>';
					/* ]]> */    
				</script>
			<?php
		}
		add_action( 'wp_head', 'gravatar_signup_encouragement_load_thickbox', 0 );
		
		function gravatar_signup_encouragement_inline_thickbox() {
			global $user_email;
			
			if ( is_user_logged_in() ) {
				$commenter_email = $user_email;
			} else {
				$commenter = wp_get_current_commenter();
				$commenter_email = $commenter['comment_author_email'];
			}
			
			/* http://www.rahulsingla.com/blog/2010/02/thickbox-show-on-page-load
				http://hobione.wordpress.com/2007/12/28/jquery-thickbox/ via http://www.webmasterworld.com/javascript/3843343.htm */
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){   
					tb_show('<?php _e( 'Signup to Gravatar', 'gse_textdomain' ); ?>', '#TB_inline?width=<?php echo apply_filters('gse_after_commenting_modal_width', '450'); ?>&height=<?php echo apply_filters('gse_after_commenting_modal_height', '435'); ?>&inlineId=gseaftercommenting&modal=true', false);
				});
			</script>
			
			<?php /* http://jquery.com/demo/thickbox/ */ ?>
			<div id="gseaftercommenting" style="display:none">
				<div style="text-align: center;" id="gse_after_commenting_modal_avatar"><?php echo get_avatar($commenter_email); ?></div>
				<p id="gse_after_commenting_modal_text"><?php echo apply_filters('gse_message_after_commenting_modal', gravatar_signup_encouragement_message($commenter_email, 'tb_remove()'), $commenter_email); ?></p>
				<p style="text-align:center" id="gse_after_commenting_modal_buttons">
					<input type="submit" id="gse_after_commenting_modal_signup_button" value="<?php _e( 'Get a new avatar', 'gse_textdomain' ); ?>" onclick="window.open('<?php echo gravatar_signup_encouragement_locale_signup_url($commenter_email); ?>'); tb_remove()" />
					<input type="submit" id="gse_after_commenting_modal_close_button" value="<?php _e( 'Close this message', 'gse_textdomain' ); ?>" onclick="tb_remove()" />
				</p> 
			</div>
		<?php }
		add_action( 'wp_footer', 'gravatar_signup_encouragement_inline_thickbox', 20 );
	}
}

/*
* Add variable in comment redirect URL
*/
function gravatar_signup_encouragement_after_commenting_redirect($url, $comment) {
	$gse_options = gravatar_signup_encouragement_get_option();
	if ( ( $gse_options['show_after_commenting_modal_unreg'] && !is_user_logged_in() ) || ( $gse_options['show_after_commenting_modal_reg'] && is_user_logged_in() ) ) {
		if (!gravatar_signup_encouragement_check_gravatar_existence($comment->comment_author_email)) {
			$new_url = add_query_arg( 'gseaftercommentingmodal', '', $url );
			return $new_url;
		} else {
			return $url;
		}
	} else {
		return $url;
	}
}

/*
* Actions to use modal after comment posting
*/
add_filter( 'comment_post_redirect', 'gravatar_signup_encouragement_after_commenting_redirect', 10, 2 );



/*
* Add encouragement in admin notices
*/

function show_gravatar_signup_encouragement_admin_notice() {
	global $user_email;
	
	if (!gravatar_signup_encouragement_check_gravatar_existence($user_email)) {
		echo '<div class="update-nag" id="gse_admin_notice">' . gravatar_signup_encouragement_message($user_email) . '</div>';
	}
}

/*
* Add encouragement on profile page
*/

function show_gravatar_signup_encouragement_profile() {
	global $user_email;
	$gse_options = gravatar_signup_encouragement_get_option();
      
	//echo '<div id="gravatar_on_profile"></div>';
	?>
<script language="javascript">
jQuery(document).ready(function()
{
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:"<?php echo $user_email; ?>" } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = "<?php echo $user_email; ?>"; <?php // pick up e-mail address from wp_usermeta ?>
			
			jQuery('#gse_profile_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("<?php echo $gse_options['below_profile']; ?>").after("<br /><div id='gse_profile_message'><?php echo apply_filters('gse_message_profile', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
          }  				
        });
});
</script>
	<?php
}

/*
* Add encouragement on registration page
* Actions based on plugin Gravajax Registration ( http://www.epicalex.com/gravajax-registration/ ) by Alex Cragg
*/
function show_gravatar_signup_encouragement_registration() {
	$gse_options = gravatar_signup_encouragement_get_option();
	?>
<script language="javascript">
jQuery(document).ready(function()
{
	<?php
	/*
	* Hack for delaying keyup event
	* Based on script from jQuery's mailing list by Klaus Hartl (  http://www.nabble.com/how-to-delay-a-event--td12288007s27240.html#a12291809 )
	*/
	?>
	var delayed; 
	jQuery("#user_email").keyup(function() <?php // when user leave #user_email field ?>
	{		
		clearTimeout(delayed);
		var value = this.value; delayed = setTimeout(function() { 
			<?php // post and check if gravatar exists or not from ajax ?>
			jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:value } ,function(data)
			{
			  if(data) <?php // if gravatar doesn't exist ?>
			  {
				var emailValue = jQuery("#user_email").val(); <?php // pick up e-mail address from field ?>
				
				jQuery('#gse_registration_message').hide(); <?php // hide tip if allready shown ?>

				jQuery("<?php echo $gse_options['below_registration']; ?>").after("<div id='gse_registration_message'><?php echo apply_filters('gse_message_after_commenting_modal', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
			  }
			  else
			  {
				jQuery('#gse_registration_message').hide(); <?php // hide tip if allready shown ?>
			  }
			});
		}, <?php echo apply_filters('gse_timeout_registration', '1000'); ?>); 
 
	});
});
</script>
<?php	
}
function gravatar_signup_encouragement_enqueing_registration() {
	wp_enqueue_script('jquery');
}

/**
 * Show encouragement in admin bar
 *
 * @since 3.0
 */
function gravatar_signup_encouragement_admin_bar() {
	global $wp_admin_bar, $user_email;
	
	/* If user has an gravatar, return empty */
	if ( gravatar_signup_encouragement_check_gravatar_existence( $user_email ) )
		return;

	$wp_admin_bar->add_menu( 
		array(
			'id' => 'gse-tip',
			'title' => __( 'Signup to Gravatar', 'gse_textdomain' ),
			'href' => gravatar_signup_encouragement_locale_signup_url( $user_email )
		)
	);
}

/**
 * Show encouragement in bbPress reply form
 *
 * @since 3.0
 */
function gravatar_signup_encouragement_bbpress() {
	global $user_email;
	$gse_options = gravatar_signup_encouragement_get_option();
	?>
	
<script language="javascript">
jQuery(document).ready(function()
{		
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:"<?php echo $user_email; ?>" } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = "<?php echo $user_email; ?>"; <?php // pick up e-mail address from wp_usermeta ?>
			
			jQuery('#gse_bbpress_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("<?php echo $gse_options['below_bbpress']; ?>").after("<br /><div id='gse_bbpress_message'><?php echo apply_filters('gse_message_bbpress', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
          }  				
        });
});
</script>
	<?php
}

/*
* Add encouragement on signup page (multisite)
*/
function show_gravatar_signup_encouragement_ms_signup() {
	$gse_options = gravatar_signup_encouragement_get_option();
	?>
<script language="javascript">
jQuery(document).ready(function()
{
	<?php
	/*
	* Hack for delaying keyup event
	* Based on script from jQuery's mailing list by Klaus Hartl (  http://www.nabble.com/how-to-delay-a-event--td12288007s27240.html#a12291809 )
	*/
	?>
	var delayed; 
	jQuery("#user_email").keyup(function() <?php // when user leave #user_email field ?>
	{		
		clearTimeout(delayed);
		var value = this.value; delayed = setTimeout(function() { 
			<?php // post and check if gravatar exists or not from ajax ?>
			jQuery.post("<?php echo gravatar_signup_encouragement_check_url(); ?>",{ gravmail:value } ,function(data)
			{
			  if(data) <?php // if gravatar doesn't exist ?>
			  {
				var emailValue = jQuery("#user_email").val(); <?php // pick up e-mail address from field ?>
				
				jQuery('#gse_ms_signup_message').hide(); <?php // hide tip if allready shown ?>

				jQuery("<?php echo $gse_options['below_ms_signup']; ?>").after("<div id='gse_ms_signup_message'><?php echo apply_filters('gse_message_after_commenting_modal', gravatar_signup_encouragement_message()); ?></div>"); <?php // show tip ?>
			  }
			  else
			  {
				jQuery('#gse_ms_signup_message').hide(); <?php // hide tip if allready shown ?>
			  }
			});
		}, <?php echo apply_filters('gse_timeout_ms_signup', '1000'); ?>); 
 
	});
});
</script>
<?php	
}
function gravatar_signup_encouragement_enqueing_ms_signup() {
	wp_enqueue_script('jquery');
}


/*
* Show notice after upgrade to version 2.0
*/
function gravatar_signup_encouragement_notice_upgrade_1_to_2() {
	if ( !current_user_can( 'manage_options' ) ) //Short circuit it.
		return;

	echo '<div class="error default-password-nag">';
	echo '<p>';
	echo '<strong>' . __('Notice:', 'gse_textdomain') . '</strong> ';
	_e('The latest version of the Gravatar Signup Encouragement plugin has new options. Default settings have been configured for these options. Would you like to review and update these new options?', 'gse_textdomain');
	echo '</p><p>';
	printf( '<a href="%s">' . __('Yes (edit Gravatar Signup Encouragement settings)', 'gse_textdomain') . '</a> | ', admin_url('options-discussion.php') . '#gravatar_signup_encouragement_form' );
	printf( '<a href="%s" id="gse-notice-1-to-2-no">' . __('No (use pre-configured default settings)', 'gse_textdomain') . '</a>', '?gse_notice_1_to_2=0' );
	echo '</p></div>';
}

/*
* Remove notice after upgrade to version 2.0
*/
function gravatar_signup_encouragement_notice_upgrade_1_to_2_handler($errors = false) {
	$gse_options = gravatar_signup_encouragement_get_option();
	
	if ( isset($_GET['gse_notice_1_to_2']) && '0' == $_GET['gse_notice_1_to_2'] ) {
		unset ($gse_options['notice_upgrade_1_to_2']);
		update_option('gravatar_signup_encouragement_settings', $gse_options);
	}
}



/*
* Load Thickbox on options page
*/
function gravatar_signup_encouragement_load_thickbox_admin() {
	add_thickbox();
}
add_action( 'admin_print_styles-options-discussion.php', 'gravatar_signup_encouragement_load_thickbox_admin' );

/*
* Filter email text field for bad themes
*/
function gravatar_signup_encouragement_filter_email_source($element) {
	if ( gravatar_signup_encouragement_is_theme_in_list() ) {
		return "input[name='email']";
	} else {
		return $element;
	}
}

/*
* Check if theme doesn't follow standards
* Themes are: Carrington Blog and Mystique
*/
function gravatar_signup_encouragement_is_theme_in_list() {
	if ( in_array( get_stylesheet(), array( 'carrington-blog', 'mystique' ) ) ) {
		return true;
	} else {
		return false;
	}
}
/*
* Add filter for bad themes
*/
add_filter("gse_get_email_value_com_unreg", "gravatar_signup_encouragement_filter_email_source");

?>