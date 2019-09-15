<?php
/**
 * Plugin Name: Redirect wordpress to welcome or landing page.
 * Plugin URI: http://eslam.me
 * Description: Easy simple to the point plug-in allow you to set page so users get redirected to it if they landed on your home page or any page or post
 * Version: 2.0
 * Author: Eslam Mahmoud
 * Author URI: http://eslam.me
 * License: GPL2
 */

//Blocking direct access to the plugin
defined('ABSPATH') or die("No script kiddies please!");

//check if the function was not already defined
if( !function_exists("eslam_me_wordpress_redirect_to_landing_page")){
	function eslam_me_wordpress_redirect_to_landing_page(){
		//if the user have the cookie that say he redirected before
		//then do not redirect him again
		//cookie expires in one week
		if(isset($_COOKIE['eslam_me_wordpress_redirect_to_landing_page_url_visited']) && $_COOKIE['eslam_me_wordpress_redirect_to_landing_page_url_visited']){
			//Do nothing
		}
		elseif (is_feed()) {
			//If feed page we will do nothing or will be a bug :D
			//https://wordpress.org/support/topic/rss-feed-not-working-if-redirect-wordpress-to-welcome-or-landing-page-is-on
		}
		else{
			//get landing page url
			$eslam_me_wordpress_redirect_to_landing_page_url = get_option('eslam_me_wordpress_redirect_to_landing_page_url', false);
			//get landing page option (all || home)
			$eslam_me_wordpress_redirect_to_landing_page_for_all_pages = get_option('eslam_me_wordpress_redirect_to_landing_page_for_all_pages', false);
			//get cookie time
			$eslam_me_wordpress_redirect_to_landing_page_cookie_time = get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time', 7);
			$eslam_me_wordpress_redirect_to_landing_page_cookie_time_type = get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', 'days');
			//if user added landing page and option
			if($eslam_me_wordpress_redirect_to_landing_page_url && $eslam_me_wordpress_redirect_to_landing_page_for_all_pages){
				//if user option is "all" then redirect on all pages
				//OR if user option is "home" && the user now visiting the home page then redirect him
				if( $eslam_me_wordpress_redirect_to_landing_page_for_all_pages == 'all' || ($eslam_me_wordpress_redirect_to_landing_page_for_all_pages == 'home' && is_front_page()) ) {
					//caclulate cookie time
					switch ($eslam_me_wordpress_redirect_to_landing_page_cookie_time_type) {
						case 'days':
							$eslam_me_wordpress_redirect_to_landing_page_cookie_time = $eslam_me_wordpress_redirect_to_landing_page_cookie_time*24*60*60;
							break;
						
						case 'hours':
							$eslam_me_wordpress_redirect_to_landing_page_cookie_time = $eslam_me_wordpress_redirect_to_landing_page_cookie_time*60*60;
							break;
						
						case 'minutes':
							$eslam_me_wordpress_redirect_to_landing_page_cookie_time = $eslam_me_wordpress_redirect_to_landing_page_cookie_time*60;
							break;
					}
					//set the cookie that say the user visited the landing page
					//and set the expiration date to be one week from now
					setcookie('eslam_me_wordpress_redirect_to_landing_page_url_visited', true, time()+$eslam_me_wordpress_redirect_to_landing_page_cookie_time);
					//redirect the user to the landing page
					header("Location: ". $eslam_me_wordpress_redirect_to_landing_page_url);
					//exit the plugin script
					die();
				}
			}
		}
	}

	//add our function to the hook
	add_action('wp', 'eslam_me_wordpress_redirect_to_landing_page');
}

/** Step 2 (from text above). */
add_action( 'admin_menu', 'eslam_me_wordpress_redirect_to_landing_page_menu' );

/** Step 1. */
function eslam_me_wordpress_redirect_to_landing_page_menu() {
	add_options_page( 'Redirect to landing page plug-in', 'Redirect to welcome or landing page', 'manage_options', 'eslam_me_wordpress_redirect_to_landing_page', 'eslam_me_wordpress_redirect_to_landing_page_options' );
}

/** Step 3. */
function eslam_me_wordpress_redirect_to_landing_page_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

//start the display of the plugin page
echo '
<div class="wrap">
	<h2>Redirect wordpress to welcome or landing page.</h2>';

	//If for submitted
	if (count($_POST)){
		//if url field submitted (empty or not)
		if(isset($_POST['landing_page_url'])){
			//if url field submitted not empty
			if ($_POST['landing_page_url']) {
				if(get_option('eslam_me_wordpress_redirect_to_landing_page_url', false)) {
					update_option('eslam_me_wordpress_redirect_to_landing_page_url', $_POST['landing_page_url']);
				}else{
					add_option('eslam_me_wordpress_redirect_to_landing_page_url', $_POST['landing_page_url'], '', 'yes' );
				}

				//if all_pages field submitted
				if(isset($_POST['all_pages'])){
					if(get_option( 'eslam_me_wordpress_redirect_to_landing_page_for_all_pages', false)){
						update_option( 'eslam_me_wordpress_redirect_to_landing_page_for_all_pages', $_POST['all_pages']);
					}else{
						add_option( 'eslam_me_wordpress_redirect_to_landing_page_for_all_pages', $_POST['all_pages'], '', 'yes' );
					}
				}

				//save cookie time
				//if not sent or < 0 set it to 7 days
				if (!isset($_POST['cookie_time']) || (int) $_POST['cookie_time'] < 0) {
					$_POST['cookie_time'] = 7;
					$_POST['cookie_time_type'] = 'days';
				}
				if(get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time', false)) {
					update_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time', (int) $_POST['cookie_time']);
				}else{
					add_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time', (int) $_POST['cookie_time'], '', 'yes' );
				}

				//save cookie time type
				//if not sent or not valid value set it to days
				if (!isset($_POST['cookie_time_type']) || !in_array($_POST['cookie_time_type'], array('days', 'hours', 'minutes'))) {
					$_POST['cookie_time_type'] = 'days';
				}
				if(get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', false)) {
					update_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', $_POST['cookie_time_type']);
				}else{
					add_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', $_POST['cookie_time_type'], '', 'yes' );
				}
			} else {
				//if url field submitted empty remove the options from DB
				delete_option('eslam_me_wordpress_redirect_to_landing_page_url');
				delete_option('eslam_me_wordpress_redirect_to_landing_page_for_all_pages');
				delete_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time');
				delete_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type');
			}
			//TODO: Save the current time stamp and then check on it with cookie creation date
		}
		echo '
			<div class="updated notice is-dismissible" id="message">
				<p>Redirection settings updated.</p>
				<button class="notice-dismiss" type="button">
					<span class="screen-reader-text">Dismiss this notice.</span>
				</button>
			</div>';
	}

	echo '
	<div class="description">Easy simple to the point plug-in allow you to set page so users get redirected to it if they landed on your home page or any page or post</div>
	<div class="description">Your visitors will be redirected to the set URL if it is the first time they visit your website and for X days, hours or minutes (you set the number in the configration) if they visit again within the time they will not be redirected again.</div>
	<br>

	<h2>Redirection Settings</h2>
	<form action="" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th><label for="landing_page_url">The welcome or landing page URL</label></th>
					<td>
						<input type="text" id="landing_page_url" name="landing_page_url" value="'.get_option('eslam_me_wordpress_redirect_to_landing_page_url', '').'"/>
						<code>http://example.com/landingPage</code>
					</td>
				</tr>
				<tr>
					<th><label for="cookie_time">Redirect the visitor every</label></th>
					<td>
						<input type="text" id="cookie_time" name="cookie_time" value="'.get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time', '7').'"/>
						<select name="cookie_time_type">
							<option ' . (get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', '')=='days'?'selected="selected"':'') . ' value="days">Days</option>
							<option ' . (get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', '')=='hours'?'selected="selected"':'') . ' value="minutes">Hours</option>
							<option ' . (get_option('eslam_me_wordpress_redirect_to_landing_page_cookie_time_type', '')=='minutes'?'selected="selected"':'') . ' value="minutes">Minutes</option>
						</select>
					</td>
				</tr>
				<tr>
					<th><label>Allow redirection on all pages?</label></th>
					<td>
						<label>
							<input type="radio" name="all_pages" value="home" ' . (get_option( 'eslam_me_wordpress_redirect_to_landing_page_for_all_pages', "home")=="home"?'checked':'') . '>Home page only
						</label>
						<br>
						<label>
							<input type="radio" name="all_pages" value="all" ' . (get_option( 'eslam_me_wordpress_redirect_to_landing_page_for_all_pages', "home")=="all"?'checked':'') . '>All pages
						</label>
					</td>
				</tr>
				<tr>
					<th>
						<input value="Save Changes" type="submit" class="button-primary" id="submitbutton" />
					</th>
					<td>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
	<div style=" font-size: 18px;">
		<a href="https://goo.gl/TBGYZv" target="_blank"><img src="'.plugins_url( 'files/donate-paypal.png', __FILE__ ).'" scale="0" style="float: left; max-width: 150px; padding-right: 20px;"></a>
		<h4>Show some love &amp; <a href="https://goo.gl/TBGYZv" target="_blank">donate with paypal</a></h4>
		<p><b>Developed by: </b><a target="_blank" href="http://eslam.me">Eslam Mahmoud</a></p>
	</div>
</div>';//pend of div.wrap
}
?>