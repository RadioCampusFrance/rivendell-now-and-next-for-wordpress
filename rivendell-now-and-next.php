<?php
/**
* Plugin Name: Rivendell Now&Next collector and browser
* Plugin URI: https://github.com/RadioCampusFrance/rivendell-now-and-next-for-wordpress
* Description: Collects "Now & Next" signals from RDAirPlay (Rivendell's automation software), stores the playlist and lets the user browse the past playlist.
* Version: 1.0
* Author: Martin Kirchgessner
* Author URI: https://github.com/martinkirch
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

    private static $instance;

    public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof RivendellNowAndNext ) ) {
			self::$instance = new RivendellNowAndNext();
		}
		return self::$instance;
	}

    public function __construct () {
        register_activation_hook( __FILE__, array ( $this, 'install' ) );
        add_action( 'init', array ( $this, 'wp_init') );
        add_action( 'admin_post_nopriv_rivendell_now_and_next_store', array ( $this, 'store') );
        add_action( 'admin_post_rivendell_now_and_next_store', array ( $this, 'store') );
        add_filter( 'page_template', array ( $this, 'playlist_page') , 99 );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
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
    
	function admin_menu () {

        // This page will be under "Settings"
        add_options_page(
            "Rivendell's playlist settings",
            'Rivendell',
            'manage_options',
            'rivendell_settings',
            array( $this, 'options_page' )
        );
    }

    function admin_init () {

        register_setting( 'rivendell_settings', self::OPTION_KEY );

        add_settings_section(
            'capture_script_parameters', // ID
            'Now & Next capture parameters', // Title
            array( $this, 'empty_cb' ), // Callback
            'rivendell_settings' // Page
        );

        add_settings_field(
            'key', // ID
            'Security Key', // Title
            array( $this, 'settings_cb_key' ), // Callback
            'rivendell_settings', // Page
            'capture_script_parameters' // Section
        );

    }

    function empty_cb ( $args ) {

    }

    function settings_cb_key ( $args ) {

        $key = get_option( self::OPTION_KEY );
        printf('<input type="text" id="key" class="large-text" name="%s" value="%s">',
            self::OPTION_KEY, esc_attr( $key ));
    }
    
    function options_page () {

        if ( !current_user_can( 'manage_options' ) ) {
			return;
        }

        echo '<div class="wrap">';
		echo '<h1>'.esc_html( get_admin_page_title() ).'</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields( 'rivendell_settings' );
        do_settings_sections( 'rivendell_settings' );
        submit_button( "Save", 'primary', 'submit' );
		echo '</form>';
		echo '</div>';
    }

    /**
     * POST request to /wp-admin/admin-post.php?action=rivendell_now_and_next_store should include data:
     *  - "key" that matches the plugin configured key
     *  - "artisttitle" structured as "ARTIST___TITLE" (that's 3 underscores)
     */
    function store () {

        $given_key = stripslashes(@$_POST['key']);
        $key = get_option( self::OPTION_KEY );
        if ( empty($key) ) {
            print "The secret key is not configured. Please set a (long) one in Wordpress' parameters\n";
            return;
        } elseif ( $key != $given_key ) {
            return;
        }

        $raw_id = stripslashes(@$_POST['artisttitle']);
        preg_match("/(.*)___(.*)/", $raw_id, $matches);
        $artist = @$matches[1];
        $title = @$matches[2];
        if ( empty( $artist ) && empty( $title ) ) {
            return;
        }
        $now = strftime("%Y-%m-%d %H:%M:%S");
        
        global $wpdb;
        $table_name = self::table_name();
        $entries = $wpdb->insert($table_name, array(
            'time' => $now,
            'artist' => $artist,
            'title' => $title
        ));

        if ( $wpdb->insert_id ) {
            print "OK\n";
        }
    }
    
    function playlist_page ( $page_template ) {

        if ( is_page( 'playlist' ) ) {
            add_filter( 'the_content', array ( $this, 'list_entries'), 1 );
        }
        
        return $page_template;
    }

    function list_entries ( $content ) {

        global $wp_current_filter;

        // Don't add to get_the_excerpt because it's too early and strips tags (adding to the_excerpt is allowed)
        if ( in_array( 'get_the_excerpt', (array) $wp_current_filter ) ) {
            return $content;
        }

        global $wpdb;
        $table_name = self::table_name();
        $entries = $wpdb->get_results( "
        SELECT *
        FROM $table_name
        ORDER BY time DESC
        LIMIT 20
        ");

        $content .= "<ul>\n";
        $previous_day = null;
        foreach ( $entries as $entry ) {
            # TODO maybe need setlocale(LC_TIME, 'fr_FR.utf8','fra');
            $timestamp = strtotime($entry->time);
            $day = strftime("%A %e %B %Y", $timestamp);
            if ( $day != $previous_day ) {
                $previous_day = $day;
                $content .= "</ul>\n<h2>$day</h2>\n<ul>\n";
            }
            $time = substr($entry->time, 11, 5);
            $content .= "<li>$time $entry->artist - $entry->title</li>\n";
        }
        $content .= "</ul>\n";

        return $content;
    }
}

$rivendell_now_and_next = RivendellNowAndNext::instance();

