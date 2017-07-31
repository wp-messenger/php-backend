<?php

class LiveChat_PHP_Adapter extends LiveChat_Adapter {

	function __construct() {
		
	}
	
	function get_mysql_time_now() {
		return date('Y-m-d H.i:s');
	}

	function config() {
		// connect to wp db

		if (!defined('DB_HOST')) {
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
		}
		if (!defined('DB_TABLE_PREFIX')) {
			define('DB_TABLE_PREFIX', 'wp_');
		}
	}

	function is_user_logged_in() {
		
	}

	function start() {
		$this->config();
	}

	function get_current_user_id() {
		
	}

	function emit_event($event, $data) {
		
	}

	function run_filter($event, $data, $extra_data = array()) {
		
	}

}
