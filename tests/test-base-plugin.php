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

		$user_args = array(
			'user_pass' => 'something',
			'user_email' => 'bob@bob.com',
			'user_login' => 'rocky',
		);

		$id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$values = $this->plugin->email_user_password( $id, $user_args, '', '', '' );

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

	function tearDown(){
		parent::tearDown();
	}
}

