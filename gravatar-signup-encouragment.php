<?php
/*
Plugin Name: Gravatar Signup Encouragement
Plugin URI: http://milandinic.com/
Description: Displays message to commenters without gravatar that they don't have one with link to Gravatar's sign-up page (e-mail included).
Version: 0.94
Author: Milan Dinić
Author URI: http://milandinic.com/
Text Domain: gse_textdomain
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

global $gse_options, $gse_plugin_dir, $gse_grav_check_url, $gse_tip_text_unformated;

/*
* Find name of directory
* and make path to gravatar-check.php
*/

$gse_plugin_dir = basename(dirname(__FILE__));
$gse_grav_check_url = WP_PLUGIN_URL.'/'.$gse_plugin_dir.'/gravatar-check.php';

/*
* Load textdomain for internationalization
*/

function gravatar_signup_encouragement_textdomain() {
	global $gse_plugin_dir;
	load_plugin_textdomain( 'gse_textdomain', false, $gse_plugin_dir . '/translations');
}
add_action('init', 'gravatar_signup_encouragement_textdomain');

/*
* Load options from database
*/

$gse_options = get_option('gravatar_signup_encouragement_settings');

/*
* Localize URL
* and format it for use
*/

/* translators: Locale gravatar.com, e.g. sr.gravatar.com for Serbian */
$gse_locale_url = _x('en.gravatar.com', 'Locale gravatar.com, e.g. sr.gravatar.com for Serbian', 'gse_textdomain');
$gse_url = "http://" . $gse_locale_url . '/site/signup/" + emailValue + "';

/*
* Make placeholder for URL in settings for easier editing
*/

$gse_url_placeholder = "URL";

/*
* Default tip
*/

$gse_tip_text_unformated = sprintf(__("It seems that you don't have an avatar on Gravatar. Click <a href='%s' target='_blank'>here</a> to make one.", "gse_textdomain"), $gse_url_placeholder);
//$gse_tip_text_default = preg_replace('/^URL/', $gse_url, $gse_tip_text_unformated, 1);

/*
* Load tip from database and replace placeholder with real URL
*/

$gse_tip_text = preg_replace('/URL/', $gse_url, $gse_options['tip_text']);

/*
* Add default options on activation of plugin
*/

function gravatar_signup_encouragement_activate() {
	global $gse_options, $gse_tip_text_unformated;
  
	if (!$gse_options) {
		$gse_options['show_comments_unreg'] = '1';
		$gse_options['below_comments_unreg'] = 'comment';
		$gse_options['below_comments_reg'] = 'comment';
		$gse_options['below_profile'] = 'your-profile h3:eq(1)';
		$gse_options['below_registration'] = 'user_email';
		$gse_options['tip_text'] = $gse_tip_text_unformated;
	
		add_option('gravatar_signup_encouragement_settings', $gse_options);
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
		$settings_link = '<a href="options-discussion.php#gravatar_signup_encouragement_form">' . __('Settings') . '</a>';
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

/*function gravatar_signup_encouragement_contextual_help() {
	
	$content = __('<a href="http://wordpress.org/extend/plugins/adminimize/">Documentation</a>', 'gse_textdomain' );
	return $content;
}
add_filter( 'contextual_help', 'gravatar_signup_encouragement_contextual_help' );*/

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
//or init insted of get_header?

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
	<span id="gravatar_signup_encouragement_form"><?php _e( 'Choose where to show Gravatar Signup Tip', 'gse_textdomain' ); ?></span>
	<br />
	
	<?php // Comments for unregistered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_comments_unreg]" class="gse_show_comments_unreg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_comments_unreg']); ?> /> <?php _e( 'Comment form (unregistered users)', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_comments_unreg" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element in comment form to show Gravatar Signup Tip', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="comment" 
			<?php checked('comment', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'Comment text', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="url" 
			<?php checked('url', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'URL', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="email" 
			<?php checked('email', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" type="radio" value="submit" 
			<?php checked('submit', $gse_options['below_comments_unreg']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_unreg]" class="gse_below_comments_unreg_custom_radio" type="radio" value="<?php echo $gse_options['below_comments_unreg_custom']; ?>" 
			<?php checked($gse_options['below_comments_unreg_custom'], $gse_options['below_comments_unreg']); ?> /> <?php _e( 'Custom ID:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_comments_unreg_custom]" type="text" class="gse_below_comments_unreg_custom_text" value="<?php echo $gse_options['below_comments_unreg_custom']; ?>" />
		</div>
	<br />
	
	<?php // Comments for registered ?>
	<label><input name="gravatar_signup_encouragement_settings[show_comments_reg]" class="gse_show_comments_reg" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_comments_reg']); ?> /> <?php _e( 'Comment form (registered users)', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_comments_reg" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element in comment form to show Gravatar Signup Tip', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" type="radio" value="comment" 
			<?php checked('comment', $gse_options['below_comments_reg']); ?> /> <?php _e( 'Comment text', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" type="radio" value="commentform p:first" 
			<?php checked('commentform p:first', $gse_options['below_comments_reg']); ?> /> <?php _e( 'Logout URL', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" type="radio" value="submit" 
			<?php checked('submit', $gse_options['below_comments_reg']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_comments_reg]" class="gse_below_comments_reg_custom_radio" type="radio" value="<?php echo $gse_options['below_comments_reg_custom']; ?>" 
			<?php checked($gse_options['below_comments_reg_custom'], $gse_options['below_comments_reg']); ?> /> <?php _e( 'Custom ID:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_comments_reg_custom]" type="text" class="gse_below_comments_reg_custom_text" value="<?php echo $gse_options['below_comments_reg_custom']; ?>" />
		</div>
	<br />
	
	<?php // Profile ?>
	<label><input name="gravatar_signup_encouragement_settings[show_profile]" class="gse_show_profile" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_profile']); ?> /> <?php _e( 'Profile page', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_profile" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element on profile page to show Gravatar Signup Tip', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="your-profile h3:eq(1)" 
			<?php checked('your-profile h3:eq(1)', $gse_options['below_profile']); ?> /> <?php _e( '“Name” header', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="user_login + .description" 
			<?php checked('user_login + .description', $gse_options['below_profile']); ?> /> <?php _e( 'User name', 'gse_textdomain' ); ?> </label><br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="display_name" 
			<?php checked('display_name', $gse_options['below_profile']); ?> /> <?php _e( 'Nicename', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="email" 
			<?php checked('email', $gse_options['below_profile']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="your-profile h3:eq(3)" 
			<?php checked('your-profile h3:eq(3)', $gse_options['below_profile']); ?> /> <?php _e( '“About Yourself” header', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" type="radio" value="description + br + .description" 
			<?php checked('description + br + .description', $gse_options['below_profile']); ?> /> <?php _e( 'Biographical Info', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_profile]" class="gse_below_profile_custom_radio" type="radio" value="<?php echo $gse_options['below_profile_custom']; ?>" 
			<?php checked($gse_options['below_profile_custom'], $gse_options['below_profile']); ?> /> <?php _e( 'Custom ID:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_profile_custom]" type="text" class="gse_below_profile_custom_text" value="<?php echo $gse_options['below_profile_custom']; ?>" />
		</div>
	<br />
	
	<?php // Registration ?>
	<label><input name="gravatar_signup_encouragement_settings[show_registration]" class="gse_show_registration" type="checkbox" value="1" 
	<?php checked('1', $gse_options['show_registration']); ?> /> <?php _e( 'Registration page', 'gse_textdomain' ); ?> </label>
		<?php // Then we print selection of cases where on page to show tip ?>
		<div id="gse_below_registration" style="margin: 5px 0 0 10px;">
			<span><?php _e( 'Choose below which text input field or element on registration page to show Gravatar Signup Tip', 'gse_textdomain' ); ?></span>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" type="radio" value="user_email" 
			<?php checked('user_email', $gse_options['below_registration']); ?> /> <?php _e( 'E-mail address', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" type="radio" value="user_login" 
			<?php checked('user_login', $gse_options['below_registration']); ?> /> <?php _e( 'User name', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" type="radio" value="wp-submit" 
			<?php checked('wp-submit', $gse_options['below_registration']); ?> /> <?php _e( 'Submit button', 'gse_textdomain' ); ?> </label>
			<br />
			<label><input name="gravatar_signup_encouragement_settings[below_registration]" class="gse_below_registration_custom_radio" type="radio" value="<?php echo $gse_options['below_registration_custom']; ?>" 
			<?php checked($gse_options['below_registration_custom'], $gse_options['below_registration']); ?> /> <?php _e( 'Custom ID:', 'gse_textdomain' ); ?></label> <input name="gravatar_signup_encouragement_settings[below_registration_custom]" type="text" class="gse_below_registration_custom_text" value="<?php echo $gse_options['below_registration_custom']; ?>" /> 
		</div>
	<br />
	
	
	<br /><br />
	<?php _e( "Text to show to commenters that don't have avatar on Gravatar.", 'gse_textdomain' ); ?><br />
	<?php _e( 'You should leave <strong>URL</strong> since it is automaticaly replaced with appropiate link to signup page on gravatar.com.', 'gse_textdomain' ); ?><br />
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
	
	<?php 
	/*
	* Get value from text input field of custom ID on keyup
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
});
</script>
<?php }

//add action so that functions could actually work
add_action('admin_init', 'add_gravatar_signup_encouragement_settings_field');



/*
* Add encouragement on comment form for unregistered users
*/
		
function show_gravatar_signup_encouragement_com_unreg() {
	global $gse_options, $gse_tip_text, $gse_grav_check_url;
	?>
	
<script language="javascript">
jQuery(document).ready(function()
{
	jQuery("#email").blur(function() <?php // when user leave #email field ?>
	{		
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo $gse_grav_check_url; ?>",{ gravmail:jQuery(this).val() } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = jQuery("#email").val(); <?php // pick up e-mail address from field ?>
			
			jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("#<?php echo $gse_options['below_comments_unreg']; ?>").after("<br /><div id='gse_comments_message'><?php echo $gse_tip_text; ?></div>"); <?php // show tip ?>
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
	global $user_email, $gse_options, $gse_tip_text, $gse_grav_check_url;
	?>
	
<script language="javascript">
jQuery(document).ready(function()
{		
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo $gse_grav_check_url; ?>",{ gravmail:"<?php echo $user_email; ?>" } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = "<?php echo $user_email; ?>"; <?php // pick up e-mail address from wp_usermeta ?>
			
			jQuery('#gse_comments_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("#<?php echo $gse_options['below_comments_reg']; ?>").after("<br /><div id='gse_comments_message'><?php echo $gse_tip_text; ?></div>"); <?php // show tip ?>
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
* Add encouragement on profile page
*/

function show_gravatar_signup_encouragement_profile() {
	global $user_email, $gse_options, $gse_tip_text, $gse_grav_check_url;
      
	//echo '<div id="gravatar_on_profile"></div>';
	?>
<script language="javascript">
jQuery(document).ready(function()
{
		<?php // post and check if gravatar exists or not from ajax ?>
		jQuery.post("<?php echo $gse_grav_check_url; ?>",{ gravmail:"<?php echo $user_email; ?>" } ,function(data)
        {
		  if(data) <?php // if gravatar doesn't exist ?>
		  {
			var emailValue = "<?php echo $user_email; ?>"; <?php // pick up e-mail address from wp_usermeta ?>
			
			jQuery('#gse_profile_message').hide(); <?php // hide tip if allready shown ?>

		  	jQuery("#<?php echo $gse_options['below_profile']; ?>").after("<br /><div id='gse_profile_message'><?php echo $gse_tip_text; ?></div>"); <?php // show tip ?>
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
	global $user_email, $gse_options, $gse_tip_text, $gse_grav_check_url;
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
			jQuery.post("<?php echo $gse_grav_check_url; ?>",{ gravmail:value } ,function(data)
			{
			  if(data) <?php // if gravatar doesn't exist ?>
			  {
				var emailValue = jQuery("#user_email").val(); <?php // pick up e-mail address from field ?>
				
				jQuery('#gse_registration_message').hide(); <?php // hide tip if allready shown ?>

				jQuery("#<?php echo $gse_options['below_registration']; ?>").after("<div id='gse_registration_message'><?php echo $gse_tip_text; ?></div>"); <?php // show tip ?>
			  }  				
			});
		}, 1000); 
 
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
Остало:
- видети како изабрати елементе на страници сложеније са нпр. your-profile h3:eq(2) и додати још подразумеваних елемената
- блокирање уноса прилагођеног приликом нештиклирања радија

?>