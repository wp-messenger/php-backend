<?php

if (!class_exists('LiveChat_API')) {

	class LiveChat_API {

		var $data = null;
		var $db = null;
		var $mime_types = null;
		var $adapter_key = null;
		var $adapter = null;
		static private $instance = array();

		static function get_instance($adapter_key) {
			if (empty(LiveChat_API::$instance) || !isset(LiveChat_API::$instance[$adapter_key])) {
				LiveChat_API::$instance[$adapter_key] = new LiveChat_API($adapter_key);
			}
			return LiveChat_API::$instance[$adapter_key];
		}

		function init_adapter() {
			// load adapters to allow it to modify the settings
			require_once("adapters/adapter_abstract.php");
			require 'adapters/' . strtolower($this->adapter_key) . '.php';
			$adapter_class = 'LiveChat_' . strtoupper($this->adapter_key) . '_Adapter';
			$this->adapter = new $adapter_class();
		}

		private function __construct($adapter_key) {
			require_once("db.php");
			$this->adapter_key = $adapter_key;

			$this->init_adapter();
			$this->adapter->start();

			require 'config.php';

			$this->db = new db();
			$this->db->connect();

			if (isset($_REQUEST['install_8123lasdzBdke'])) {
				$this->db->create_tables();
				die('tables created');
			}

			$this->start_api_listener();
		}

		function start_api_listener() {
			if ($this->is_valid_api_request()) {
				$this->process_http_api_request();
			}
		}

		function setup_mime_types() {
			$allowed_mime_types = array(
				// Image formats.
				'jpg' => 'image/jpeg',
				'jpeg' => 'image/jpeg',
				'jpe' => 'image/jpeg',
				'gif' => 'image/gif',
				'png' => 'image/png',
				'bmp' => 'image/bmp',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'ico' => 'image/x-icon',
				// Text formats.
				'txt' => 'text/plain',
				'csv' => 'text/csv',
				'tsv' => 'text/tab-separated-values',
				'ics' => 'text/calendar',
				'rtx' => 'text/richtext',
				'css' => 'text/css',
				'html' => 'text/html',
				'htm' => 'text/html',
				// Audio formats.
				'mp3' => 'audio/mpeg',
				'm4a' => 'audio/mpeg',
				'm4b' => 'audio/mpeg',
				'wav' => 'audio/wav',
				'midi' => 'audio/midi',
				'wma' => 'audio/x-ms-wma',
				// Misc application formats.
				'rtf' => 'application/rtf',
				'pdf' => 'application/pdf',
				'tar' => 'application/x-tar',
				'zip' => 'application/zip',
				'gz' => 'application/x-gzip',
				'gzip' => 'application/x-gzip',
				'rar' => 'application/rar',
				'7z' => 'application/x-7z-compressed',
				'psd' => 'application/octet-stream',
				// MS Office formats.
				'doc' => 'application/msword',
				'pot' => 'application/vnd.ms-powerpoint',
				'pps' => 'application/vnd.ms-powerpoint',
				'ppt' => 'application/vnd.ms-powerpoint',
				'wri' => 'application/vnd.ms-write',
				'xla' => 'application/vnd.ms-excel',
				'xls' => 'application/vnd.ms-excel',
				'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
				'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
				'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
				'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
				'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
				'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
				'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
				'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
				'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
				'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
				'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
				'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
				'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
				'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
				'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
				'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
				'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
				// OpenOffice formats.
				'odt' => 'application/vnd.oasis.opendocument.text',
				'odp' => 'application/vnd.oasis.opendocument.presentation',
				'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				'odg' => 'application/vnd.oasis.opendocument.graphics',
				'odc' => 'application/vnd.oasis.opendocument.chart',
				'odb' => 'application/vnd.oasis.opendocument.database',
				'odf' => 'application/vnd.oasis.opendocument.formula',
				// iWork formats.
				'key' => 'application/vnd.apple.keynote',
				'numbers' => 'application/vnd.apple.numbers',
				'pages' => 'application/vnd.apple.pages',
			);
			$this->mime_types = $allowed_mime_types;
//        $this->d( $this->mime_types );
		}

		function is_api_request() {
			if (isset($_REQUEST['livechat_request']) &&
					$_REQUEST['livechat_request'] === 'yes' &&
					!empty($_REQUEST['endpoint'])) {
				return true;
			} else {
				return false;
			}
		}

		function is_valid_api_request() {
			// @todo add token authentication
			if ($this->is_api_request() &&
					$this->is_user_logged_in()) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * Dump
		 * 
		 * Dump any variable
		 * .
		 * @param int|string|array|object $var
		 * 
		 * @since 1.0
		 */
		function d($var) {
			echo '<pre>';
			var_dump($var);
			echo '</pre>';
		}

		function is_user_logged_in() {
//        d( is_user_logged_in() );
//        die();
			return $this->adapter->is_user_logged_in();
		}

		function cleanup_data($data) {
			return $this->sanitize($data);
		}

		function array_map_recursive($f, $xs, $numeric_f = false) {
			$out = [];
			foreach ($xs as $k => $x) {
				if (is_array($x)) {
					$out[$k] = $this->array_map_recursive($f, $x);
				} elseif (is_numeric($x) && $numeric_f) {
					$out[$k] = $numeric_f($x);
				} else {
					$out[$k] = $f($x);
				}
			}
			return $out;
		}

		function sanitize($var) {
			if (is_array($var)) {
				$var = $this->array_map_recursive('strip_tags', $var);
				$var = $this->array_map_recursive('htmlspecialchars', $var, 'intval');
			} elseif (is_string($var)) {
				$var = strip_tags(htmlspecialchars($var));
			} elseif (is_numeric($var)) {
				$var = intval($var);
			}
			return $var;
		}

		function process_http_api_request() {
			if (!$this->is_valid_api_request()) {
				die('Not valid');
			}
			$this->data = $this->cleanup_data($_REQUEST);

			$endpoint = $this->data['endpoint'];
			$this->$endpoint();

			$this->emit_event('before_http_api_response', array(
				'endpoint' => $endpoint,
			));
			die();
		}

		function get_current_user_id() {
			return $this->adapter->get_current_user_id();
		}

		function run_filter($event, $data = array(), $extra_data = array()) {
			$extra_data['current_user_id'] = $this->get_current_user_id();
			$extra_data['instance'] = $this;

			$extra_data = array_merge($this->data, $extra_data);
			return $this->adapter->run_filter('livechat_' . $event, $data, $extra_data);
		}

		function emit_event($event, $data = array()) {
			$data['current_user_id'] = $this->get_current_user_id();
			$data['instance'] = $this;

			$data = array_merge($this->data, $data);
			$this->adapter->emit_event('livechat_' . $event, $data);
		}

		function addFriend() {

			if ((int) $this->check_user() === 1) {
				echo json_encode(1);
				return;
			}
			$id = $this->get_current_user_id();
			$user_id = $this->data["id"];

			$this->db->queryDB("INSERT INTO " . DB_TABLE_FRIENDS . " VALUES (null, '$id','$user_id')");
			echo json_encode(1);
		}

		function check_user() {


			$id = $this->get_current_user_id();
			$user_id = $this->data["id"];

			if ($this->db->numR("SELECT * FROM " . DB_TABLE_FRIENDS . " WHERE (user='$id' AND " . DB_TABLE_FRIENDS . ".with='$user_id') OR (user='$user_id' AND " . DB_TABLE_FRIENDS . ".with='$id')") > 0) {
				echo json_encode($user_id);
			} else {
				echo json_encode(-1);
			}
		}

		function download_proxy() {

			//$this->d( $this->get_current_user_id() );

			if ($this->get_current_user_id()) {
				$id = $this->get_current_user_id();
			} else {
				header("HTTP/1.0 404 Not Found");
				die();
			}
			$mimeTypes = array(
				'doc' => 'application/msword',
				'pdf' => 'application/pdf',
			);
			// set the file here (best of using a $_GET[])


			$link = $this->sanitize($_GET["file"]);
			$result = $this->db->getone("SELECT * FROM " . DB_TABLE_FILES . " WHERE link='$link'");
			$check = $this->db->numR("SELECT * FROM " . DB_TABLE_MESSAGES . " WHERE (user_sender='$id' OR user_receiver='$id') AND message LIKE '%" . $link . "%'");

			/* $this->d( $link );
			  $this->d( $result );
			  $this->d( $check ); */

			if (!$check || !$result) {
				header("HTTP/1.0 404 Not Found");
				die();
			}
			$file = FILES_LOCATION . $result["link"] . "." . $result["ext"];

			/* $this->d( $file );
			  $this->d( file_exists( $file ) );
			  die(); */
			if (!file_exists($file)) {
				header("HTTP/1.0 404 Not Found");
				die();
			}
			// gets the extension of the file to be loaded for searching array above
			$ext = explode('.', $file);
			$ext = end($ext);

			// gets the file name to send to the browser to force download of file
			$fileName = explode("/", $file);
			$fileName = end($fileName);

			$lower_ext = strtolower($ext);
//        $this->d( $lower_ext );
			if ($lower_ext === 'png' || $lower_ext === 'jpg' || $lower_ext === 'jpeg') {
				header('Content-Type: image/jpeg');
				readfile($file);
				die();
			}

			// opens the file for reading and sends headers to browser
			$fp = fopen($file, "r");

			if (!empty($mimeTypes[$ext])) {
				header("Content-Type: " . $mimeTypes[$ext]);
			}
			header('Content-Disposition: attachment; filename="' . $result["real_name"] . '"');

			// reads file and send the raw code to browser
			while (!feof($fp)) {
				$buff = fread($fp, 4096);
				echo $buff;
			}
			// closes file after whe have finished reading it
			fclose($fp);
			exit();
		}

		function get_diff_for_query($timeNow = null) {
			if (!$timeNow) {
				$timeNow = date('Y-m-d H:i:s');
			}
			return $this->db->getone("SELECT TIMESTAMPDIFF(SECOND,NOW(),'$timeNow') AS dif");
		}

		function get_all_messages() {

			$id = $this->get_current_user_id();
			$user_id = $this->data["user_id"];
			$timeNow = $this->get_time_now();


//        $skip = 1 * $this->data["page"];
			$posts_per_page = 20;
			$skip = ( $this->data["page"] < 2 ) ? 0 : ( $this->data["page"] - 1) * (int) $posts_per_page;

			$dif = $this->get_diff_for_query($timeNow);
			$this->db->queryDB("UPDATE " . DB_TABLE_MESSAGES . " SET " . DB_TABLE_MESSAGES . ".read = 1 WHERE user_sender='$user_id' AND user_receiver='$id'");
			$sql = "SELECT user_sender,user_receiver,message,DATE_ADD(time, INTERVAL " . $dif["dif"] . " second) as m_time FROM " . DB_TABLE_MESSAGES . " WHERE (user_sender='$id' AND user_receiver='$user_id') OR (user_sender='$user_id' AND user_receiver='$id') ORDER BY (time) DESC LIMIT " . $skip . ",$posts_per_page";



			$result = $this->db->fetch($sql);


			echo json_encode($result);
		}

		function get_time_now() {
			return ( empty($this->data["timeNow"])) ? date('Y-m-d H:i:s') : $this->data["timeNow"];
		}

		function get_user_last_online() {
			$id = $this->data["id"];


			$timeNow = $this->get_time_now();
			$dif = $this->get_diff_for_query($timeNow);
			$result = $this->db->getone("SELECT DATE_ADD(" . DB_TABLE_LAST_ONLINE . ".time, INTERVAL " . $dif["dif"] . " second) as m_time FROM " . DB_TABLE_LAST_ONLINE . " WHERE id='$id'");

			echo json_encode((!$result) ? -1 : $result);
		}

		function delete_files() {
			$id = $this->data["id"];
			$this->db->queryDB("DELETE FROM " . DB_TABLE_FILES . " WHERE `user_id` = :user_id", array(
				':user_id' => $id,
			));
		}

		function delete_contacts() {
			$id = $this->data["id"];
			$this->db->queryDB("DELETE FROM " . DB_TABLE_FRIENDS . " WHERE `user` = :user_id or `with` = :user_id", array(
				':user_id' => $id,
			));
		}

		function read_image() {

			$_GET['file'] = $this->sanitize($_GET["image"]);
			$this->download_proxy();
			/* if ($this->get_current_user_id()) {
			  $id = $this->get_current_user_id();
			  } else {
			  exit();
			  }

			  $link = $this->sanitize( $_GET["image"] );
			  $result = $this->db->getone("SELECT * FROM " . DB_TABLE_FILES . " WHERE link='$link'");
			  $check = $this->db->numR("SELECT * FROM " . DB_TABLE_MESSAGES . " WHERE (user_sender='$id' OR user_receiver='$id') AND message LIKE '%" . $link . "%'");

			  if ( ! $check || ! $result ) {
			  header("HTTP/1.0 404 Not Found");
			  die();
			  }
			  if( ! file_exists( FILES_LOCATION . $result["link"] . "." . $result["ext"] )){
			  header("HTTP/1.0 404 Not Found");
			  die();
			  }
			  header('Content-Type: image/jpeg');
			  readfile( FILES_LOCATION . $result["link"] . "." . $result["ext"]);
			  die(); */
		}

		function read_users() {

//       $this->d('called');
			$id = $this->get_current_user_id();
			$timeNow = $this->get_time_now();


			$dif = $this->get_diff_for_query($timeNow);
			$sql = "SELECT id,display_name,DATE_ADD(m_time, INTERVAL " . $dif["dif"] . " second) as m_time,image,
        new_messages FROM (SELECT " . DB_TABLE_USERS . ".ID as id," . DB_TABLE_USERS . ".display_name as display_name," . DB_TABLE_MESSAGES . ".time as m_time," . DB_TABLE_AVATARS . ".user_image as image, new_messages
        FROM " . DB_TABLE_USERS . "
        INNER JOIN " . DB_TABLE_FRIENDS . " ON (" . DB_TABLE_FRIENDS . ".user='$id' AND " . DB_TABLE_FRIENDS . ".with=" . DB_TABLE_USERS . ".ID) OR (" . DB_TABLE_FRIENDS . ".user=" . DB_TABLE_USERS . ".ID AND " . DB_TABLE_FRIENDS . ".with='$id')
        LEFT JOIN " . DB_TABLE_MESSAGES . " ON (" . DB_TABLE_MESSAGES . ".user_sender='$id' AND " . DB_TABLE_MESSAGES . ".user_receiver=" . DB_TABLE_USERS . ".ID) OR (" . DB_TABLE_MESSAGES . ".user_sender=" . DB_TABLE_USERS . ".ID AND " . DB_TABLE_MESSAGES . ".user_receiver='$id')
        LEFT JOIN " . DB_TABLE_AVATARS . " ON " . DB_TABLE_AVATARS . ".user_id=" . DB_TABLE_USERS . ".ID
        LEFT JOIN (SELECT (COUNT(" . DB_TABLE_MESSAGES . ".read)-SUM(" . DB_TABLE_MESSAGES . ".read)) as new_messages," . DB_TABLE_MESSAGES . ".user_receiver as user_receiver," . DB_TABLE_MESSAGES . ".user_sender as user_sender FROM " . DB_TABLE_MESSAGES . " WHERE " . DB_TABLE_MESSAGES . ".user_receiver='$id' GROUP BY(user_sender)) b ON b.user_receiver='$id' AND b.user_sender=" . DB_TABLE_USERS . ".ID
        ORDER BY(" . DB_TABLE_MESSAGES . ".time) DESC) as newTable GROUP BY id ORDER BY m_time DESC";

//       $this->d($id);
//       $this->d($timeNow);
//       $this->d($dif);
//       echo ($sql);
			$result = $this->db->fetch($sql);


			echo json_encode($result);
		}

		function read_messages() {



			$id = $this->get_current_user_id();
			$user_id = $this->data["id"];

			$this->db->queryDB("UPDATE " . DB_TABLE_MESSAGES . " SET " . DB_TABLE_MESSAGES . ".read = 1 WHERE user_sender='$user_id' AND user_receiver='$id'");
			echo json_encode(1);
		}

		function search_messages() {

			$id = $this->get_current_user_id();
			$user_id = $this->data["user_id"];
			$timeNow = $this->get_time_now();
			$search = $this->data["search"];


			$dif = $this->get_diff_for_query($timeNow);
			$this->db->queryDB("UPDATE " . DB_TABLE_MESSAGES . " SET " . DB_TABLE_MESSAGES . ".read = 1 WHERE user_sender='$user_id' AND user_receiver='$id'");
			$sql = "SELECT user_sender,user_receiver,message,DATE_ADD(time, INTERVAL " . $dif["dif"] . " second) as m_time FROM " . DB_TABLE_MESSAGES . " WHERE ((user_sender='$id' AND user_receiver='$user_id') OR (user_sender='$user_id' AND user_receiver='$id')) AND message LIKE '%" . $search . "%' ORDER BY (time)";

			$result = $this->db->fetch($sql);


			echo json_encode($result);
		}

		function search_users() {


			$id = $this->get_current_user_id();
			$timeNow = $this->get_time_now();
			$like = $this->data["like"];

			$dif = $this->get_diff_for_query($timeNow);
			$sql = "SELECT id,display_name,DATE_ADD(m_time, INTERVAL " . $dif["dif"] . " second) as m_time,image,
            new_messages FROM (SELECT " . DB_TABLE_USERS . ".ID as id," . DB_TABLE_USERS . ".display_name as display_name," . DB_TABLE_MESSAGES . ".time as m_time," . DB_TABLE_AVATARS . ".user_image as image, new_messages
            FROM " . DB_TABLE_USERS . "
            INNER JOIN " . DB_TABLE_FRIENDS . " ON ((" . DB_TABLE_FRIENDS . ".user='$id' AND " . DB_TABLE_FRIENDS . ".with=" . DB_TABLE_USERS . ".ID) OR (" . DB_TABLE_FRIENDS . ".user=" . DB_TABLE_USERS . ".ID AND " . DB_TABLE_FRIENDS . ".with='$id')) AND " . DB_TABLE_USERS . ".display_name LIKE '%" . $like . "%'
            LEFT JOIN " . DB_TABLE_MESSAGES . " ON (" . DB_TABLE_MESSAGES . ".user_sender='$id' AND " . DB_TABLE_MESSAGES . ".user_receiver=" . DB_TABLE_USERS . ".ID) OR (" . DB_TABLE_MESSAGES . ".user_sender=" . DB_TABLE_USERS . ".ID AND " . DB_TABLE_MESSAGES . ".user_receiver='$id')
            LEFT JOIN " . DB_TABLE_AVATARS . " ON " . DB_TABLE_AVATARS . ".user_id=" . DB_TABLE_USERS . ".ID
            LEFT JOIN (SELECT (COUNT(" . DB_TABLE_MESSAGES . ".read)-SUM(" . DB_TABLE_MESSAGES . ".read)) as new_messages," . DB_TABLE_MESSAGES . ".user_receiver as user_receiver," . DB_TABLE_MESSAGES . ".user_sender as user_sender FROM " . DB_TABLE_MESSAGES . " WHERE " . DB_TABLE_MESSAGES . ".user_receiver='$id' GROUP BY(user_sender)) b ON b.user_receiver='$id' AND b.user_sender=" . DB_TABLE_USERS . ".ID
            ORDER BY(" . DB_TABLE_MESSAGES . ".time) DESC) as newTable GROUP BY id ORDER BY m_time DESC";
			$result = $this->db->fetch($sql);


			echo json_encode($result);
		}

		function search_users_one_letter() {


			$id = $this->get_current_user_id();
			$timeNow = $this->get_time_now();
			$like = $this->data["like"];

			$dif = $this->get_diff_for_query($timeNow);
			$sql = "SELECT id,display_name,DATE_ADD(m_time, INTERVAL " . $dif["dif"] . " second) as m_time,image,
            new_messages FROM (SELECT " . DB_TABLE_USERS . ".ID as id," . DB_TABLE_USERS . ".display_name as display_name," . DB_TABLE_MESSAGES . ".time as m_time," . DB_TABLE_AVATARS . ".user_image as image, new_messages
            FROM " . DB_TABLE_USERS . "
            INNER JOIN " . DB_TABLE_FRIENDS . " ON ((" . DB_TABLE_FRIENDS . ".user='$id' AND " . DB_TABLE_FRIENDS . ".with=" . DB_TABLE_USERS . ".ID) OR (" . DB_TABLE_FRIENDS . ".user=" . DB_TABLE_USERS . ".ID AND " . DB_TABLE_FRIENDS . ".with='$id')) AND " . DB_TABLE_USERS . ".display_name LIKE '" . $like . "%'
            LEFT JOIN " . DB_TABLE_MESSAGES . " ON (" . DB_TABLE_MESSAGES . ".user_sender='$id' AND " . DB_TABLE_MESSAGES . ".user_receiver=" . DB_TABLE_USERS . ".ID) OR (" . DB_TABLE_MESSAGES . ".user_sender=" . DB_TABLE_USERS . ".ID AND " . DB_TABLE_MESSAGES . ".user_receiver='$id')
            LEFT JOIN " . DB_TABLE_AVATARS . " ON " . DB_TABLE_AVATARS . ".user_id=" . DB_TABLE_USERS . ".ID
            LEFT JOIN (SELECT (COUNT(" . DB_TABLE_MESSAGES . ".read)-SUM(" . DB_TABLE_MESSAGES . ".read)) as new_messages," . DB_TABLE_MESSAGES . ".user_receiver as user_receiver," . DB_TABLE_MESSAGES . ".user_sender as user_sender FROM " . DB_TABLE_MESSAGES . " WHERE " . DB_TABLE_MESSAGES . ".user_receiver='$id' GROUP BY(user_sender)) b ON b.user_receiver='$id' AND b.user_sender=" . DB_TABLE_USERS . ".ID
            ORDER BY(" . DB_TABLE_MESSAGES . ".time) DESC) as newTable GROUP BY id ORDER BY m_time DESC";
			$result = $this->db->fetch($sql);


			echo json_encode($result);
		}

		function send_message() {

			if (empty($this->data["message"]) || empty($this->data["to"])) {
				echo json_encode(-1);
				die();
			}
			$id = $this->get_current_user_id();
			$to = $this->data["to"];
			$msg = $this->run_filter('message_content_before_insert', nl2br($this->data["message"]));

			$this->db->queryDB("INSERT INTO " . DB_TABLE_MESSAGES . " (user_sender,user_receiver,message,`time`) VALUES ('$id','$to','$msg',NOW())");
			echo json_encode(1);
		}

		function unread_messages_count() {
			$id = (!$this->data["id"] ) ? $this->get_current_user_id() : $this->data["id"];


			$result = $this->db->numR("SELECT * FROM `" . DB_TABLE_MESSAGES . "` WHERE `user_receiver` = $id AND `read` = 0");


			echo json_encode($result);
		}

		function update_last_online() {
			$id = $this->get_current_user_id();
			$now = $this->adapter->get_mysql_time_now();

			$this->db->queryDB("REPLACE INTO " . DB_TABLE_LAST_ONLINE . " (id, " . DB_TABLE_LAST_ONLINE . ".time) values('$id','$now')");
			echo json_encode(1);
		}

		function get_random_string($length = 8, $valid_chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789') {
			// start with an empty random string
			$random_string = "";
			// count the number of chars in the valid chars string so we know how many choices we have
			$num_valid_chars = strlen($valid_chars);
			// repeat the steps until we've created a string of the right length
			for ($i = 0; $i < $length; $i++) {
				// pick a random number from 1 up to the number of valid chars
				$random_pick = mt_rand(1, $num_valid_chars);
				// take the random character out of the string of valid chars
				// subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
				$random_char = $valid_chars[$random_pick - 1];
				// add the randomly-chosen char onto the end of our string so far
				$random_string .= $random_char;
			}
			// return our finished random string
			return $random_string;
		}

		function upload() {

			$_FILES = $this->sanitize($_FILES);
			$id = $this->get_current_user_id();

			$this->setup_mime_types();

			// exit if multiple files were uploaded
//        $this->d( $_FILES['file']);
			if (is_array($_FILES["file"]["name"])) {
				echo json_encode(-1);
				die();
			}

			$temp = explode(".", $_FILES["file"]["name"]);
			$fileNameOld = $_FILES["file"]["name"];
			$extension = end($temp);

//        $this->d( $temp );
//        $this->d( $fileNameOld );
//        $this->d(  $extension );
//        $this->d(  $this->mime_types );
//        $this->d(  $this->mime_types[$extension] );
			// verify files extension
			if (empty($this->mime_types[$extension])) {
				echo json_encode(-1);
				die();
			}
			$newName = $this->sanitize($_POST["newName"]);
			$to = $this->sanitize($_POST["to_user"]);
			$filename = $newName . "." . $extension;
			move_uploaded_file($_FILES["file"]["tmp_name"], FILES_LOCATION . $filename);
			$oldName = $this->sanitize($_POST["label"]);


			$this->db->queryDB("INSERT INTO " . DB_TABLE_FILES . " (link,ext,real_name, user_id) VALUES ('$newName','$extension','$fileNameOld', $id)");
			$tmp = array(
				0 => $newName,
				1 => $extension,
				2 => $to,
				3 => $fileNameOld,
			);

			echo json_encode($tmp);
			die();
		}

		function get($method, $data = array()) {
			$this->data = $this->cleanup_data($data);

			$endpoint = $method;

			ob_start();

			$this->$endpoint();

			$response = json_decode(ob_get_clean(), true);

			$this->emit_event('before_api_response', array(
				'endpoint' => $endpoint,
				'response' => $response,
			));

			return $response;
		}

	}

}

//setup: 1- select adapter
LiveChat_API::get_instance('wp');

if (!function_exists('livechat_api_get')) {

	function livechat_api_get($adapter = 'wp', $method, $data) {
		$chat = LiveChat_API::get_instance($adapter);

		return $chat->get($method, $data);
	}

}