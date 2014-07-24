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

	function tearDown(){
		parent::tearDown();
	}
}

