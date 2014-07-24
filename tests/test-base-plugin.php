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

