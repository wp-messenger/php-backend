<?php


class db {

    var $db;

    function __construct() {
	}
	
    function connect() {
        try {
            $this->db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        } catch (PDOException $e) {
            die("Connection failed: " . $this->db->connect_error);
        }
    }

    function queryDB($sql, $params = array()) {
        $sth = $this->db->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sth->execute($params);

        $result = $sth->fetchAll( PDO::FETCH_ASSOC );
        
//        var_dump( $sql );
//        var_dump( $result );
        return ( empty($result) ) ? false : $result;
    }

    function fetch($sql) {
//        $data = array();
        $result = $this->queryDB($sql);

        if (!$result) {
            return array();
        }
//        var_dump( $sql );
//        var_dump( $result );
//        while( $result ) {
//            $data[] = $row;
//        }
        return $result;
    }

    function numR($sql) {
        $result = $this->db->query($sql);
//        var_dump( $result );
//        var_dump( $result->rowCount() );
        return ( $result ) ? $result->rowCount() : 0;
    }

    function getone($sql) {
        $result = $this->queryDB($sql);

        if ( ! $result ) {
            return false;
        }
        return current( $result );
    }

        // backwards compatibility
    function closeDB() {
    }

    function create_tables() {
    $tables = array(
            'files' => " CREATE TABLE IF NOT EXISTS ".DB_TABLE_FILES." (
	`link` VARCHAR(25) NOT NULL DEFAULT '',
	`ext` VARCHAR(10) NOT NULL DEFAULT '',
	`real_name` VARCHAR(100) NOT NULL DEFAULT '',
	`ID` INT(11) NOT NULL AUTO_INCREMENT,
	`user_id` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`ID`),
	UNIQUE INDEX `ID` (`ID`)
)",
            'friends' => " CREATE TABLE IF NOT EXISTS ".DB_TABLE_FRIENDS." (
	`ID` INT(11) NOT NULL AUTO_INCREMENT,
	`user` INT(11) NOT NULL DEFAULT '0',
	`with` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`ID`),
	UNIQUE INDEX `ID` (`ID`)
)",
            'avatars' => " CREATE TABLE IF NOT EXISTS ".DB_TABLE_AVATARS." (
	`user_id` INT(11) NOT NULL DEFAULT '0',
	`user_image` VARCHAR(200) NOT NULL DEFAULT '',
	PRIMARY KEY (`user_id`),
	UNIQUE INDEX `user_id` (`user_id`)
)",
            'last_online_status' => " CREATE TABLE IF NOT EXISTS ".DB_TABLE_LAST_ONLINE." (
	`id` INT(11) NOT NULL,
	`time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`id`)
)",
            'messages' => " CREATE TABLE IF NOT EXISTS ".DB_TABLE_MESSAGES." (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`user_sender` INT(11) NOT NULL DEFAULT '0',
	`user_receiver` INT(11) NOT NULL DEFAULT '0',
	`message` TEXT NOT NULL DEFAULT '',
	`time` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`read` INT(11) NOT NULL DEFAULT '0',
	PRIMARY KEY (`id`)
)",
            'users' => " CREATE TABLE IF NOT EXISTS ".DB_TABLE_USERS." (
	`ID` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`user_login` VARCHAR(60) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`user_pass` VARCHAR(64) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`user_nicename` VARCHAR(50) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`user_email` VARCHAR(100) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`user_url` VARCHAR(100) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`user_registered` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`user_activation_key` VARCHAR(60) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	`user_status` INT(11) NOT NULL DEFAULT '0',
	`display_name` VARCHAR(250) NOT NULL DEFAULT '' COLLATE 'utf8mb4_unicode_ci',
	PRIMARY KEY (`ID`),
	INDEX `user_login_key` (`user_login`),
	INDEX `user_nicename` (`user_nicename`)
)"
        );
    foreach ($tables as $table) {
     $this->db->exec($table);    
}
}
}