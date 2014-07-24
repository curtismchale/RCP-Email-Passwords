<?php
/*
Plugin Name: RCP Password Email
Plugin URI: http://sfndesign.ca
Description: Sends plaintext passwords with the RCP CSV Import
Version: 1.0
Author: SFNdesign, Curtis McHale
Author URI: http://sfndesign.ca
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

class RCP_CSV_Email_Pass{

	function __construct(){

		add_action( 'rcp_user_import_user_added', array( $this, 'email_user_password' ), 10, 5 );

		add_action( 'admin_notices', array( $this, 'check_required_plugins' ) );

		// Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );

	} // construct

	/**
	 * Emails users information about their newly created account on our membership site.
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @param int       $user_id                required        user id that wp_insert_user just created
	 * @param array     $user_args              required        Array of args that were passed to wp_insert_user
	 * @param int       $subscription_id        required        ID of the subscription for the user
	 * @param string    $status                 required        The status of the subscription
	 * @param int       $expiration             required        Expiration date of the subscription
	 * @uses site_url()                                         Returns URL for the site
	 * @uses esc_url()                                          Makes sure out data is safe
	 * @uses esc_attr()                                         Escaping for safety
	 * @uses wp_mail()                                          WP email function
	 */
	public function email_user_password( $user_id, $user_args, $subscription_id, $status, $expiration  ){

		$to = $user_args['user_email'];

		$site    = site_url();
		$subject = apply_filters( 'rcp_email_passwords_email_subject', 'A new user account created for you at '. esc_url( $site ) .'.', $user_id, $user_args, $subscription_id, $status, $expiration );

		$password  = $user_args['user_pass'];
		$user_name = $user_args['user_login'];

		$message = $subject . '<br />';
		$message .= 'username: '. esc_attr( $user_name ) .'<br />';
		$message .= 'password: '. esc_attr( $password ) .'<br />';

		wp_mail( $to, $subject, apply_filters( 'rcp_email_passwords_message', $message, $user_id, $user_args, $subscription_id, $status, $expiration ) );

		$return_value = array(
			'to' => $to,
			'subject' => $subject,
			'message' => $message,
			'site' => site_url(),
			'id' => $user_id,
		);

		return $return_value;

	} // email_user_password

	/**
	 * Checks for WooCommerce and GF and kills our plugin if they aren't both active
	 *
	 * @uses    function_exists     Checks for the function given string
	 * @uses    deactivate_plugins  Deactivates plugins given string or array of plugins
	 *
	 * @action  admin_notices       Provides WordPress admin notices
	 *
	 * @since   1.0
	 * @author  SFNdesign, Curtis McHale
	 */
	public function check_required_plugins(){

		if( ! is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ) ){ ?>

			<div id="message" class="error">
				<p>RCP Password Email expects Restrict Content Pro to be active. This plugin has been deactivated.</p>
			</div>

			<?php
			deactivate_plugins( '/rcp-email-passwords/rcp-email-passwords.php' );
		} // if rcp active

		if( ! is_plugin_active( 'restrict-content-pro-csv-user-import/rcp-user-import.php' ) ){ ?>

			<div id="message" class="error">
				<p>RCP Password Email expects Restrict Content Pro CSV User Import to be active. This plugin has been deactivated.</p>
			</div>

			<?php
			deactivate_plugins( '/rcp-email-passwords/rcp-email-passwords.php' );
		} // if rcp active

	} // check_required_plugins

	/**
	 * Fired when plugin is activated
	 *
	 * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
	 */
	public function activate( $network_wide ){

	} // activate

	/**
	 * Fired when plugin is deactivated
	 *
	 * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
	 */
	public function deactivate( $network_wide ){

	} // deactivate

	/**
	 * Fired when plugin is uninstalled
	 *
	 * @param   bool    $network_wide   TRUE if WPMU 'super admin' uses Network Activate option
	 */
	public function uninstall( $network_wide ){

	} // uninstall

} // RCP_CSV_Email_Pass

$GLOBALS['rcp_email_passwords'] = new RCP_CSV_Email_Pass();
