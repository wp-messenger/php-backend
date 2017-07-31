<?php

class LiveChat_WP_Adapter extends LiveChat_Adapter {

	function __construct() {
		
	}
	
	function get_mysql_time_now() {
		return current_time('mysql', false );
	}

	function config() {
		// connect to wp db
		/* if (!defined('DB_HOST')) {
		  define('DB_HOST', 'localhost');
		  }
		  if (!defined('DB_NAME')) {
		  define('DB_NAME', 'chat_alex_b');
		  }
		  if (!defined('DB_USER')) {
		  define('DB_USER', 'chat_test');
		  }
		  if (!defined('DB_PASSWORD')) {
		  define('DB_PASSWORD', '1ODuG4V6Xi');
		  } */

		if (!defined('WP_LOCATION')) {
			// actualizar path
			define('WP_LOCATION', dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))));
		}
	}

	function run_filter($event, $data, $extra_data = array() ) {
		return apply_filters($event, $data, $extra_data );
	}

	function emit_event($event, $data) {
		do_action($event, $data);
	}

	function is_user_logged_in() {
		return is_user_logged_in();
	}

	function start() {

		// exit if wp is already loaded
		if (function_exists('wp_get_current_user')) {
			return;
		}

		$this->config();

		define('WP_USE_THEMES', false); // Do not use the theme files
		define('COOKIE_DOMAIN', false); // Do not append verify the domain to the cookie
		define('DISABLE_WP_CRON', true); // We don't want extra things running...
//$_SERVER['HTTP_HOST'] = ""; // For multi-site ONLY. Provide the 
// URL/blog you want to auth to.
// Path (absolute or relative) to where your WP core is running
		require( WP_LOCATION . "/wp-load.php");


		if (!defined('DB_TABLE_PREFIX')) {
			global $wpdb;
			define('DB_TABLE_PREFIX', $wpdb->prefix);
		}
	}

	function get_current_user_id() {
		return get_current_user_id();
	}

}
