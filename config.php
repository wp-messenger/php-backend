<?php

// tables names
if( ! defined( 'DB_TABLE_FRIENDS' ) ){
    define( 'DB_TABLE_FRIENDS', DB_TABLE_PREFIX . 'friends' );
}
if( ! defined( 'DB_TABLE_FILES' ) ){
    define( 'DB_TABLE_FILES', DB_TABLE_PREFIX . 'files' );
}
if( ! defined( 'DB_TABLE_AVATARS' ) ){
    define( 'DB_TABLE_AVATARS', DB_TABLE_PREFIX . 'avatars' );
}
if( ! defined( 'DB_TABLE_LAST_ONLINE' ) ){
    define( 'DB_TABLE_LAST_ONLINE', DB_TABLE_PREFIX . 'last_online_status' );
}
if( ! defined( 'DB_TABLE_MESSAGES' ) ){
    define( 'DB_TABLE_MESSAGES', DB_TABLE_PREFIX . 'messages' );
}
if( ! defined( 'DB_TABLE_USERS' ) ){
    define( 'DB_TABLE_USERS', DB_TABLE_PREFIX . 'users' );
}

if( ! defined( 'FILES_LOCATION' ) ){
    define( 'FILES_LOCATION', __DIR__ . '/files/' );
}