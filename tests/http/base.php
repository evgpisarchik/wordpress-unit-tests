<?php
/**
 * Note, When running these tests, remember that some things are done differently
 * based on safe_mode. You can run the test in safe_mode like such:
 *
 *   phpunit -d safe_mode=on --group http
 *
 * You may also need `-d safe_mode_gid=1` to relax the safe_mode checks to allow
 * inclusion of PEAR.
 *
 * The WP_HTTP tests require a class-http.php file of r17550 or later.
 */
abstract class WP_HTTP_UnitTestCase extends WP_UnitTestCase {
	// You can use your own version of data/WPHTTP-testcase-redirection-script.php here.
	var $redirection_script = 'http://api.wordpress.org/core/tests/1.0/redirection.php';

	function setUp() {

		if ( is_callable( array('WP_HTTP', '_getTransport') ) ) {
			$this->markTestSkipped('The WP_HTTP tests require a class-http.php file of r17550 or later.');
			return;
		}

		$class = "WP_HTTP_" . $this->transport;
		if ( !call_user_func( array($class, 'test') ) ) {
			$this->markTestSkipped( sprintf('The transport %s is not supported on this system', $this->transport) );
		}

		// Disable all transports aside from this one.
		foreach ( array( 'curl', 'streams', 'fsockopen' ) as $t ) {
			remove_filter( "use_{$t}_transport", '__return_false' ); // Just strip them all
			if ( $t != $this->transport )
				add_filter( "use_{$t}_transport", '__return_false' ); // and add it back if need be..
		}
	}

	function tearDown() {
		foreach ( array( 'curl', 'streams', 'fsockopen' ) as $t ) {
			remove_filter( "use_{$t}_transport", '__return_false' );
		}
		parent::tearDown();
	}

	function test_redirect_on_301() {
		// 5 : 5 & 301
		$res = wp_remote_request($this->redirection_script . '?code=301&rt=' . 5, array('redirection' => 5) );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals(200, (int)$res['response']['code'] );
	}

	function test_redirect_on_302() {
		// 5 : 5 & 302
		$res = wp_remote_request($this->redirection_script . '?code=302&rt=' . 5, array('redirection' => 5) );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals(200, (int)$res['response']['code'] );
	}

	/**
	 * @ticket 16855
	 */
	function test_redirect_on_301_no_redirect() {
		// 5 > 0 & 301
		$res = wp_remote_request($this->redirection_script . '?code=301&rt=' . 5, array('redirection' => 0) );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals(301, (int)$res['response']['code'] );
	}

	/**
	 * @ticket 16855
	 */
	function test_redirect_on_302_no_redirect() {
		// 5 > 0 & 302
		$res = wp_remote_request($this->redirection_script . '?code=302&rt=' . 5, array('redirection' => 0) );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals(302, (int)$res['response']['code'] );
	}

	function test_redirections_equal() {
		// 5 - 5
		$res = wp_remote_request($this->redirection_script . '?rt=' . 5, array('redirection' => 5) );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals(200, (int)$res['response']['code'] );
	}

	function test_no_head_redirections() {
		// No redirections on HEAD request:
		$res = wp_remote_request($this->redirection_script . '?code=302&rt=' . 1, array('method' => 'HEAD') );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals( 302, (int)$res['response']['code'] );
	}

	/**
	 * @ticket 16855
	 */
	function test_redirect_on_head() {
		// Redirections on HEAD request when Requested
		$res = wp_remote_request($this->redirection_script . '?rt=' . 5, array('redirection' => 5, 'method' => 'HEAD') );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals( 200, (int)$res['response']['code'] );
	}

	function test_redirections_greater() {
		// 10 > 5
		$res = wp_remote_request($this->redirection_script . '?rt=' . 10, array('redirection' => 5) );
		$this->assertTrue( is_wp_error($res), print_r($res, true) );
	}

	function test_redirections_greater_edgecase() {
		// 6 > 5 (close edgecase)
		$res = wp_remote_request($this->redirection_script . '?rt=' . 6, array('redirection' => 5) );
		$this->assertTrue( is_wp_error($res) );
	}

	function test_redirections_less_edgecase() {
		// 4 < 5 (close edgecase)
		$res = wp_remote_request($this->redirection_script . '?rt=' . 4, array('redirection' => 5) );
		$this->assertFalse( is_wp_error($res) );
	}

	/**
	 * @ticket 16855
	 */
	function test_redirections_zero_redirections_specified() {
		// 0 redirections asked for, Should return the document?
		$res = wp_remote_request($this->redirection_script . '?code=302&rt=' . 5, array('redirection' => 0) );
		$this->assertFalse( is_wp_error($res) );
		$this->assertEquals( 302, (int)$res['response']['code'] );
	}

	/**
	 * Do not redirect on non 3xx status codes
	 *
	 * @ticket 16889
	 */
	function test_location_header_on_201() {
		// Prints PASS on initial load, FAIL if the client follows the specified redirection
		$res = wp_remote_request( $this->redirection_script . '?201-location=true' );
		$this->assertFalse( is_wp_error( $res ) );
		$this->assertEquals( 'PASS', $res['body']);
	}

	/**
	 * @ticket 11888
	 */
	function test_send_headers() {
		// Test that the headers sent are recieved by the server
		$headers = array('test1' => 'test', 'test2' => 0, 'test3' => '');
		$res = wp_remote_request( $this->redirection_script . '?header-check', array('headers' => $headers) );

		$this->assertFalse( is_wp_error($res) );

		$headers = array();
		foreach ( explode("\n", $res['body']) as $key => $value ) {
			if ( empty($value) )
				continue;
			$parts = explode(':', $value,2);
			unset($heaers[$key]);
			$headers[ $parts[0] ] = $parts[1];
		}

		$this->assertTrue( isset($headers['test1']) && 'test' == $headers['test1'] );
		$this->assertTrue( isset($headers['test2']) && '0' === $headers['test2'] );
		// cURL/HTTP Extension Note: Will never pass, cURL does not pass headers with an empty value.
		// Should it be that empty headers with empty values are NOT sent?
		//$this->assertTrue( isset($headers['test3']) && '' === $headers['test3'] );
	}

	function test_file_stream() {
		$url = 'http://unit-tests.svn.wordpress.org/trunk/data/images/2004-07-22-DSC_0007.jpg'; // we'll test against a file in the unit test data
		$size = 87348;
		$res = wp_remote_request( $url, array( 'stream' => true, 'timeout' => 30 ) ); //Auto generate the filename.

		$this->assertFalse( is_wp_error( $res ) );
		$this->assertEquals( '', $res['body'] ); // The body should be empty.
		$this->assertEquals( $size, $res['headers']['content-length'] ); // Check the headers are returned (and the size is the same..)
		$this->assertEquals( $size, filesize($res['filename']) ); // Check that the file is written to disk correctly without any extra characters

		unlink($res['filename']); // Remove the temporary file
	}
}
