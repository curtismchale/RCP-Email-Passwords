<?php

class TestBaseRCPEmailPasswords extends WP_UnitTestCase {

	private $plugin;

	function setUp(){
		parent::setUp();
		$this->plugin = $GLOBALS['rcp_email_passwords'];
	}

	/**
	 * Tests to make sure that our global $var that is our plugin is around
	 *
	 * @since 0.0.1
	 * @author SFNdesign, Curtis McHale
	 */
	function testPluginActive(){
		$this->assertFalse( null == $this->plugin, 'testPluginActive says our plugin isn not loaded' );
	}

	/**
	 * This tests the default output of the password email
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testPasswordEmail(){

		$user_args = $this->set_args();

		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$values = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$this->assertTrue( $values['id'] == $id );

		// test the user email
		$this->assertTrue( $values['to'] === $user_args['user_email'] );

		// test the subject
		$subject = 'A new user account created for you at ' . $values['site'] . '.';
		$this->assertTrue( $values['subject'] === $subject );

		// testing the mesage output
		$message = $subject . '<br />';
		$message .= 'username: '. $user_args['user_login'] .'<br />';
		$message .= 'password: '. $user_args['user_pass'] .'<br />';
		$this->assertTrue( $values['message'] == $message );

	}

	/**
	 * Makes sure that our filter is working for changing the subjects of emails
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testSubjectFilter(){

		$user_args = $this->set_args();

		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		add_filter( 'rcp_email_passwords_email_subject', array( $this, 'change_email_subject' ), 10, 6 );
		$values = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$subject = 'New subject with '. $id .'and' . $user_args['user_email'].'.';
		$this->assertTrue( $values['subject'] == $subject );


		remove_filter( 'rcp_email_passwords_email_subject', array( $this, 'change_email_subject' ), 10, 6 );

	}

	/**
	 * Tests the message filter in the plugin
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testMessageFilter(){

		$user_args = $this->set_args();

		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		add_filter( 'rcp_email_passwords_message', array( $this, 'change_email_message' ), 10, 6 );
		$values = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$message = 'Hey you have a username '.$user_args['user_login'] . 'and password'. $user_args['user_pass'].' now.';
		$this->assertTrue( $values['message'] == $message );


		remove_filter( 'rcp_email_passwords_message', array( $this, 'change_email_message' ), 10, 6 );
	}

	/**
	 * Tests to make sure that the deactivation routines are fired if we don't have our base plugins
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testPluginDeactivate(){

		ob_start();
		$this->plugin->check_required_plugins();
		$s = ob_get_contents();
		ob_clean();

		$expected_output = $this->expected_output();

		// have to pass them through the same formatting to match against
		$s = preg_replace( '/\s+/', '', $s );
		$expected_output = preg_replace( '/\s+/', '', $expected_output );

		$this->assertTrue( $s === $expected_output );
	}

	/**
	 * Making sure we handle if there is no user_id passed
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testNoUserID(){

		$user_args = $this->set_args();

		$output = $this->plugin->email_user_password( 'string', $user_args, '', '', '' );

		$this->assertTrue( false === $output, 'You did not have a user_id passed' );
	}

	/**
	 * Tests to make sure that we catch no email case
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testNoEmail(){

		$user_args = $this->set_args();
		unset( $user_args['user_email'] );
		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$output = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$this->assertTrue( false === $output, 'We did not deal with a blank email' );

	}

	/**
	 * Makes sure we deal with bad data in the email field
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testBadEmail(){

		$user_args = $this->set_args();
		$user_args['user_email'] = 'string';
		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$output = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$this->assertTrue( false === $output, 'We did not detect a string that is not an email' );

	}

	/**
	 * Makes sure we test for something in the user_login
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testNoLoginName(){

		$user_args = $this->set_args();
		unset( $user_args['user_login'] );
		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$output = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$this->assertTrue( false === $output, 'We did not deal with a blank login name' );

	}

	/**
	 * Tests to make sure that we deal with a not valid user_id
	 *
	 * @since 1.1
	 * @author SFNdesign, Curtis McHale
	 */
	function testValidUserID(){

		$user_args = $this->set_args();
		$id = rand( '50000', '100000');

		$output = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

		$this->assertTrue( false === $output, 'We did not deal with user_id that is not a valid user' );

	}

	/**
	 * Our expected HTML output when we check for plugin activation
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @return string
	 */
	function expected_output(){

		$html = '<div id="message" class="error">';
				$html .= '<p>RCP Password Email expects Restrict Content Pro to be active. This plugin has been deactivated.</p>';
		$html .= '</div>';

		$html .= '<div id="message" class="error">';
				$html .= '<p>RCP Password Email expects Restrict Content Pro CSV User Import to be active. This plugin has been deactivated.</p>';
		$html .= '</div>';

		return $html;
	}
	/**
	 * Changes the email message the user gets
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @param $message
	 * @param $user_id
	 * @param $user_args
	 * @param $subscription_id
	 * @param $status
	 * @param $expiration
	 * @return string
	 */
	function change_email_message( $message, $user_id, $user_args, $subscription_id, $status, $expiration ){
		return 'Hey you have a username '.$user_args['user_login'] . 'and password'. $user_args['user_pass'].' now.';
	}

	/**
	 * Filters the subject so that we can test our subject filter
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @param $message
	 * @param $user_id
	 * @param $user_args
	 * @param $subscription_id
	 * @param $status
	 * @param $expiration
	 * @return string
	 */
	function change_email_subject( $message, $user_id, $user_args, $subscription_id, $status, $expiration ){
		return 'New subject with '. $user_id .'and' . $user_args['user_email'].'.';
	}

	/**
	 * Setting our default args for our user array
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @return array
	 */
	function set_args(){

		$user_args = array(
			'user_pass' => 'something',
			'user_email' => 'bob@bob.com',
			'user_login' => 'rocky',
		);

		return $user_args;
	}

	function tearDown(){
		parent::tearDown();
	}
}

