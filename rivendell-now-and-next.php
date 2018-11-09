<?php
/**
* Plugin Name: Rivendell Now&Next collector and browser
* Plugin URI: TODO
* Description: Collects "Now & Next" signals from RDAirPlay (Rivendell's automation software), stores the playlist and lets the user browse the past playlist.
* Version: 1.0
* Author: Martin Kirchgessner
* Author URI: https://github.com/radiocampusgrance
* License: GPLv2
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class RivendellNowAndNext {

    const SCHEMA_VERSION = "1.0";
    const OPTION_DB_VERSION = "rivendell_now_and_next_db_version";
    const OPTION_KEY = "rivendell_now_and_next_key";

    static function table_name () {

        global $wpdb;
        return $wpdb->prefix . "rivendell_playlist";
    }

    public function __construct () {
        register_activation_hook( __FILE__, array ( $this, 'install' ) );
        add_action( 'init', array ( $this, 'wp_init') );
        add_action( 'admin_post_nopriv_store', array ( $this, 'store') );
        add_action( 'admin_post_store', array ( $this, 'store') );
        add_filter( 'page_template', array ( $this, 'show') , 99 );
    }

    function install () {

        $installed_version = get_option( self::OPTION_DB_VERSION );

        if ( $installed_version != self::SCHEMA_VERSION ) {

            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();
        
            $table_name = self::table_name();
            $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            artist text NOT NULL,
            title text NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";
        
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
            update_option( self::OPTION_DB_VERSION, self::SCHEMA_VERSION );
        }

        if ( !url_to_postid('playlist') ) {
            wp_insert_post( array(
                'post_title' => 'playlist',
                'post_content' => '',
                'post_status' => 'publish',
                'post_type' => 'page'
            ) );
        }
    }

    function wp_init () {

        add_option( self::OPTION_KEY );
        add_option( self::OPTION_DB_VERSION );
        //TODO load_plugin_textdomain('rivendell-now-and-next', false, basename( dirname( __FILE__ ) ) . '/lang' 
    }
    
    /**
     * request to /wp-admin/admin-post.php?action=rivendell_now_and_next_store
     * you should POST : 
     *  - "key" that matches the plugin configured key
     *  - "artisttitle" structured as "ARTIST___TITLE" (that's 3 underscores)
     */
    function store () {
        
    }
    
    function show( $page_template ) {

        if ( is_page( 'playlist' ) ) {
            $page_template = dirname( __FILE__ ) . '/rivendell-playlist-page.php';
        }
        
        return $page_template;
    }
}

$rivendell_now_and_next = new RivendellNowAndNext();

