<?php
/*
Plugin Name: Gravatar Signup Encouragement
Plugin URI: http://blog.milandinic.com/wordpress/plugins/gravatar-signup-encouragement/
Description: Displays message to users without gravatar that they don't have one with link to Gravatar's sign-up page (e-mail included).
Version: 1.1
Author: Milan Dinić
Author URI: http://blog.milandinic.com/
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
* Make global variables
*/

global $gse_options;

/*
* Find name of directory
* and make path to gravatar-check.php
*/

//$gse_plugin_dir = basename(dirname(__FILE__));
//$gse_grav_check_url = plugins_url( 'gravatar-check.php', __FILE__ );

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

/*
* Load options from database
*/

$gse_options = get_option('gravatar_signup_encouragement_settings');

/*
* Add default options on activation of plugin
* or update existing on plugin upgrade
*/

function gravatar_signup_encouragement_activate() {
	global $gse_options;
  
	if (!$gse_options) {
		/*
		* Show message to unregistered commenters
		* and show below: comment field (for comments), “Name” header (profile), e-mail address (registration & signup)
		*/
		$gse_options['show_comments_unreg'] = '1';
		$gse_options['below_comments_unreg'] = '#comment';
		$gse_options['show_after_commenting_modal_unreg'] = '1';
		$gse_options['below_comments_reg'] = '#comment';
		$gse_options['below_profile'] = '#your-profile h3:eq(1)';
		$gse_options['below_registration'] = '#user_email';
		$gse_options['below_ms_signup'] = '#user_email';
		$gse_options['version'] = '1.1';
		/*
		* Load plugin textdomain only for activation hook
		* so that default message could be saved in database localized
		*/
		load_plugin_textdomain( 'gse_textdomain', false, dirname( plugin_basename( __FILE__ ) ) . '/translations');
		$gse_options['tip_text'] = sprintf(__("
It seems that you don't have an avatar on Gravatar. This means that default avatar is shown beside your comments on this site.

If you want to have your own unique avatar, click <a href='%s' target='_blank'>here</a> to make one (link opens in new tab/window).", "gse_textdomain"), 'URL');
	
		add_option('gravatar_signup_encouragement_settings', $gse_options);
	} else {
		if (!$gse_options['version']) {
			/*
			* Make array with names of options
			*/
			$elements = array('below_comments_unreg', 'below_comments_unreg_custom', 'below_comments_reg', 'below_comments_reg_custom', 'below_profile', 'below_profile_custom', 'below_registration', 'below_registration_custom');
			
			/*
			* Split array into keys with names of options
			*/
			foreach ( $elements as $element ) :
				/*
				* Check if option exists
				*/
				if ($gse_options[$element]) {
					/*
					* Get position of # in option's value.
					* If it isn't a first character,
					* add it in front of value and update option.
					*/
					if (strpos($gse_options[$element], '#') !== 0) {
						$gse_options[$element] = '#' . $gse_options[$element];
						update_option('gravatar_signup_encouragement_settings', $gse_options);
					}
				}
			endforeach;
			
			/*
			* Add new version and new default value
			*/
			$gse_options['version'] = '1.1';
			$gse_options['below_ms_signup'] = '#user_email';
			$gse_options['notice_upgrade_1_11'] = true;
			update_option('gravatar_signup_encouragement_settings', $gse_options);
		}
	}
}
register_activation_hook( __FILE__, 'gravatar_signup_encouragement_activate' );

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
		$links = array_merge( array($settings_link), $links); // before other links
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
	global $gse_options;
	if (/*!is_admin() && */is_singular() && comments_open()) {
		if ( (!is_user_logged_in() && $gse_options['show_comments_unreg']) || (is_user_logged_in() && $gse_options['show_comments_reg']) ) {
			wp_enqueue_script('jquery');
		}
	}
}
add_action('get_header', 'gravatar_signup_encouragement_enqueing_comments');
//or init instead of get_header?

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
	global $gse_options;
	// First we print selection of cases when to show tip ?>
	<span id="gravatar_signup_encouragement_form"><?php _e( 'Choose where to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
	<br />
	
	<?php // Comments for unregistered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_comments_unreg]" class="gse_show_comments_unreg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_comments_unreg']); ?> /> <?php _e( 'Comment form (unregistered users)', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_comments_unreg" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element in comment form to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
			<br />
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
	<?php checked('1', $gse_options['show_comments_reg']); ?> /> <?php _e( 'Comment form (registered users)', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_comments_reg" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element in comment form to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
			<br />
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
	<?php checked('1', $gse_options['show_after_commenting_modal_unreg']); ?> /> <?php _e( 'Dialog after comment posting (unregistered users)', 'gse_textdomain' ); ?> </label>
	<br />
	
	<?php // Modal for registered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_after_commenting_modal_reg]" class="gse_show_after_commenting_modal_reg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_after_commenting_modal_reg']); ?> /> <?php _e( 'Dialog after comment posting (registered users)', 'gse_textdomain' ); ?> </label>
	<br />
	
	<?php // Admin notice ?>
	<label><input name="gravatar_signup_encouragement_settings[show_in_admin_notices]" class="gse_show_in_admin_notices" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_in_admin_notices']); ?> /> <?php _e( 'Administration notice', 'gse_textdomain' ); ?> </label>
	<br />
	
	<?php // Profile ?>
	<label><input name="gravatar_signup_encouragement_settings[show_profile]" class="gse_show_profile" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_profile']); ?> /> <?php _e( 'Profile page', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_profile" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element on profile page to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
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
	<?php checked('1', $gse_options['show_registration']); ?> /> <?php _e( 'Registration page', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_registration" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element on registration page to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
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
	<?php checked('1', $gse_options['show_ms_signup']); ?> /> <?php _e( 'Signup page (multisite)', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_ms_signup" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element on signup page (multisite) to show Gravatar Signup Encouragement message', 'gse_textdomain' ); ?></span>
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
	
	
	<br /><br />
	<?php
	/*
	* Show upgrade notice
	*/
	if ( $gse_options['notice_upgrade_1_11'] ) {
		?><div class="dashboard-widget-notice">
		<?php _e( "There are new options for Gravatar Signup Encouragement.", "gse_textdomain" );?><br />
		<ol>
			<li><?php _e( "Now you can show message in dialog after comment is posted, as a administration notice, and on a signup page for multisite installation.", "gse_textdomain" );?></li>
			<li><?php _e( "Now you can add message after any element on a page more easily then before.", "gse_textdomain" );?></li>
			<li><?php _e( "There are new predefined elements for profile page.", "gse_textdomain" );?></li>
			<li><?php _e( "Finally, there is a new default message which is longer and more descriptive then previous one. You can take idea from it for update of your existing message.", "gse_textdomain" );?></li>
		</ol>
		<?php _e( "New default message:", "gse_textdomain" );?><br />
		<textarea  readonly="true" rows="5" cols="50" class="large-text code"><?php echo sprintf(__("
It seems that you don't have an avatar on Gravatar. This means that default avatar is shown beside your comments on this site.

If you want to have your own unique avatar, click <a href='%s' target='_blank'>here</a> to make one (link opens in new tab/window).", "gse_textdomain"), 'URL'); ?></textarea><br />
		<?php printf( '<a href="%s" id="gse-notice-1-11-no">' . __('Do not show this notice again', 'gse_textdomain') . '</a>', '?gse_notice_1_11=0' ); ?></div><br />
	<?php } ?>
	<?php _e( "Text to show to users that don't have avatar on Gravatar.", 'gse_textdomain' ); ?><br />
	<?php _e( 'You should leave <strong>URL</strong> since it is automatically replaced with appropriate link to signup page on gravatar.com.', 'gse_textdomain' ); ?><br />
	<?php _e( 'Do not use double quotes (<strong>"</strong>) since it will break code. Instead, use curly quotes (<strong>&#8220;</strong> and <strong>&#8221;</strong>) for text, and single quotes (<strong>&#039;</strong>) for HTML tags.', 'gse_textdomain' ); ?><br />
	<label><textarea name="gravatar_signup_encouragement_settings[tip_text]" rows="5" cols="50" id="gravatar_signup_encouragement_settings[tip_text]" class="large-text code"><?php echo $gse_options['tip_text']; ?></textarea></label>
	
	<?php // Last we print jQuery script for show/hide on checkbox and text-to-radio input value ?>
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
});
</script>
<?php }

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
	global $gse_options;
	
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
	* Load tip from database and replace placeholder with real URL
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
	global $gse_options;
	
	/*
	* Show message if user commented before
	*/
	if ( wp_get_current_commenter() ) {
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
	global $user_email, $gse_options;
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
	global $gse_options;
	if ( !is_user_logged_in() && $gse_options['show_comments_unreg'] ) {
		add_action('comment_form', 'show_gravatar_signup_encouragement_com_unreg');
	}
	elseif ( is_user_logged_in() && $gse_options['show_comments_reg'] ) {
		add_action('comment_form', 'show_gravatar_signup_encouragement_com_reg');
	}
}
add_action('get_header', 'gravatar_signup_encouragement_comment_form');

/*
* Add encouragement modal after comment posting
*/
function show_gravatar_signup_encouragement_after_commenting_modal() {
	global $gse_options;
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
	global $gse_options;
	if ( ( !is_user_logged_in() && $gse_options['show_after_commenting_modal_unreg'] ) || ( is_user_logged_in() && $gse_options['show_after_commenting_modal_reg'] ) ) {
		if (!gravatar_signup_encouragement_check_gravatar_existence($comment->comment_author_email)) {
			$new_url = add_query_arg( 'gseaftercommentingmodal', '', $url );
			return $new_url;
		} else {
			return $url;
		}
	}
}

/*
* Actions to use modal after comment posting
*/
add_filter('comment_post_redirect','gravatar_signup_encouragement_after_commenting_redirect',10,2);

/* http://themehybrid.com/support/topic/adding-jquery-ui-using-wp_enqueue_script-and-firing-onload-events */
if ( isset($_REQUEST['gseaftercommentingmodal']) ) {
	add_action('template_redirect', 'show_gravatar_signup_encouragement_after_commenting_modal');
}

/*
* Add encouragement in admin notices
*/

function show_gravatar_signup_encouragement_admin_notice() {
	global $user_email;
	
	if (!gravatar_signup_encouragement_check_gravatar_existence($user_email)) {
		echo '<div class="update-nag" id="gse_admin_notice">' . gravatar_signup_encouragement_message($user_email) . '</div>';
	}
}
if ( $gse_options['show_in_admin_notices'] ) {
	add_action('admin_notices', 'show_gravatar_signup_encouragement_admin_notice');
}

/*
* Add encouragement on profile page
*/

function show_gravatar_signup_encouragement_profile() {
	global $user_email, $gse_options;
      
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
if ( $gse_options['show_profile'] ) {
	add_action('show_user_profile', 'show_gravatar_signup_encouragement_profile');
}

/*
* Add encouragement on registration page
* Actions based on plugin Gravajax Registration ( http://www.epicalex.com/gravajax-registration/ ) by Alex Cragg
*/
function show_gravatar_signup_encouragement_registration() {
	global $gse_options;
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
if ( $gse_options['show_registration'] ) {
	//Actions
	add_action('login_head', 'gravatar_signup_encouragement_enqueing_registration');
	add_action('login_head', 'wp_print_scripts', 11);
	add_action('register_form', 'show_gravatar_signup_encouragement_registration');
}

/*
* Add encouragement on signup page (multisite)
*/
function show_gravatar_signup_encouragement_ms_signup() {
	global $gse_options;
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
		}, <?php echo apply_filters('gse_timeout_ms_signup', '1000'); ?>; 
 
	});
});
</script>
<?php	
}
function gravatar_signup_encouragement_enqueing_ms_signup() {
	wp_enqueue_script('jquery');
}
if ( $gse_options['show_ms_signup'] ) {
	//Actions
	add_action('init', 'gravatar_signup_encouragement_enqueing_ms_signup');
	add_action('signup_extra_fields', 'show_gravatar_signup_encouragement_ms_signup');
}

/*
* Show notice after upgrade to version 1.1
*/
function gravatar_signup_encouragement_notice_upgrade_to_1_11() {
	if ( !current_user_can( 'manage_options' ) ) //Short circuit it.
		return;

	echo '<div class="error default-password-nag">';
	echo '<p>';
	echo '<strong>' . __('Notice:', 'gse_textdomain') . '</strong> ';
	_e('Plugin Gravatar Signup Encouragement has new options in its new version. Review it since you may find something that fits your needs.', 'gse_textdomain');
	echo '</p><p>';
	printf( '<a href="%s">' . __('Yes, take me to Gravatar Signup Encouragement settings', 'gse_textdomain') . '</a> | ', admin_url('options-discussion.php') . '#gravatar_signup_encouragement_form' );
	printf( '<a href="%s" id="gse-notice-1-11-no">' . __('No thanks, do not remind me again', 'gse_textdomain') . '</a>', '?gse_notice_1_11=0' );
	echo '</p></div>';
}

/*
* Remove notice after upgrade to version 1.1
*/
function gravatar_signup_encouragement_notice_upgrade_to_1_11_handler($errors = false) {
	global $gse_options;
	
	if ( isset($_GET['gse_notice_1_11']) && '0' == $_GET['gse_notice_1_11'] ) {
		unset ($gse_options['notice_upgrade_1_11']);
		update_option('gravatar_signup_encouragement_settings', $gse_options);
	}
}

/*
* Add actions for notice after upgrade to version 1.1
*/
if ( $gse_options['notice_upgrade_1_11'] ) {
	if ( !isset($_GET['gse_notice_1_11']) ) {
		add_action('admin_notices', 'gravatar_signup_encouragement_notice_upgrade_to_1_11');
	}
	add_action('admin_init', 'gravatar_signup_encouragement_notice_upgrade_to_1_11_handler');
}


/*
* Test actions
*
* Do test actions on request
*/
/*
if ( isset($_REQUEST['gsedotestactions']) ) {
	add_action('template_redirect', 'gravatar_signup_encouragement_test_actions');
}
function gravatar_signup_encouragement_test_actions() {
	global $gse_options;
	$gse_options['notice_upgrade_1_11'] = true;
	update_option('gravatar_signup_encouragement_settings', $gse_options);
}
add_filter('gse_message_after_commenting_modal', 'gse_filter_test', 10, 2);
function gse_filter_test($message, $commenter_email) {
	$message = 'new message <a href="' . gravatar_signup_encouragement_locale_signup_url($commenter_email) . '">url</a>';
	return $message;
}
*/
/*
Остало:
- блокирање уноса прилагођеног приликом нештиклирања радија
- побољшати документацију
*/
?>