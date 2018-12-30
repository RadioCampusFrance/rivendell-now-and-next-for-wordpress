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
    const OPTION_KEEP_N_DAYS = "rivendell_now_and_next_keep_n_days";

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
        add_option( self::OPTION_KEEP_N_DAYS );
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
        register_setting( 'rivendell_settings', self::OPTION_KEEP_N_DAYS,
            array(
                'type' => 'integer',
                'sanitize_callback' => array( $this, 'sanitize_keep_n_days' ),
            ) );

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

        add_settings_field(
            'keep_n_days', // ID
            'Days before erasing playlist', // Title
            array( $this, 'settings_cb_keep_n_days' ), // Callback
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

    function settings_cb_keep_n_days ( $args ) {

        $current = get_option( self::OPTION_KEEP_N_DAYS );

        if ( $current === false ){
            $current = 7;
        }

        printf('<input type="text" id="keep_n_days" name="%s" value="%s">',
            self::OPTION_KEEP_N_DAYS, esc_attr( $current ));
    }

    function sanitize_keep_n_days( $input ) {

        if ( preg_match('/[0-9]+/', $input ) ){

            return (int) $input;

        } else {

            add_settings_error(
                'not_a_number',
                'validationError',
                'Please the number of days before erasing playlist items',
                'error');
        }
        return null;
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
        $matches = explode( '___', $raw_id );
        $artist = @$matches[0];
        $title = @$matches[1];
        if ( empty( $artist ) && empty( $title ) ) {
            return;
        }

        global $wpdb;
        $table_name = self::table_name();

        $previous = $wpdb->get_row( "
        SELECT *
        FROM $table_name
        ORDER BY time DESC
        LIMIT 1
        ");
        if ( $previous->artist == $artist && $previous->title == $title ) {
            print "Already posted, skipping\n";
            return;
        }

        $days_before_erasure = get_option( self::OPTION_KEEP_N_DAYS );
        $erase_before = new DateTime("-${days_before_erasure}days");
        $wpdb->query( $wpdb->prepare("
            DELETE FROM $table_name
            WHERE time <= %s
        ", $erase_before->format("Y-m-d H:i:s") ) );

        $entries = $wpdb->insert($table_name, array(
            'time' => current_time( 'mysql' ),
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

        $before = @$_GET['before'];
        if ( !empty($before) ) {
            if ( !preg_match('/\A[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\z/', $before ) ){
                $before = false;
            }
        }

        global $wpdb;
        $table_name = self::table_name();
        $available_hours = $wpdb->get_results( "
            SELECT DISTINCT DATE(time) as day, HOUR(time) as hour
            FROM $table_name
            ORDER BY time DESC
        ");

        $content .= "<a name='rivendellplaylist'/> </a>\n";
        $content .= "<form class='rivendell-playlist' action='#rivendellplaylist'>\n";

        $content .= "Titres diffusés vers <select name='before' onchange='this.form.submit()'>>\n";
        foreach ( $available_hours as $entry ) {
            if ( strlen( $entry->hour ) == 1) {
                $hour = '0'.$entry->hour;
            } else {
                $hour = $entry->hour;
            }
            $timestamp = strtotime($entry->day.' '.$hour.':00:00');
            $display = strftime("%H:%M, %A %e %B %Y", $timestamp);
            $value = $entry->day.' '.$hour.':59:59';
            if ( $value == $before ){
                $selected = 'selected';
            } else {
                $selected = '';
            }
            $content .= "<option value='$value' $selected>$display</option>\n";
        }
        $content .= "</select><input type='submit' value='OK'/></form>\n";

        if ( $before ){
            $where = "time <= '$before'";
        } else {
            $where = "1";
        }

        $entries = $wpdb->get_results( "
        SELECT *
        FROM $table_name
        WHERE $where
        ORDER BY time DESC
        LIMIT 20
        ");

        $content .= "<ul class='rivendell-playlist'>\n";
        $previous_day = null;
        foreach ( $entries as $entry ) {
            # TODO maybe need setlocale(LC_TIME, 'fr_FR.utf8','fra');
            $timestamp = strtotime($entry->time);
            $day = strftime("%A %e %B %Y", $timestamp);
            if ( $day != $previous_day ) {
                $previous_day = $day;
                $content .= "</ul>\n<h2 class='rivendell-playlist-day'>$day</h2>\n<ul class='rivendell-playlist'>\n";
            }
            $time = substr($entry->time, 11, 5);
            # TIP: in CSS you can select artist with "li.rivendell-playlist span:nth-of-type(2)" 
            $content .= "<li class='rivendell-playlist'><span>$time</span> <span>$entry->artist</span>: <span>$entry->title</span></li>\n";
        }
        $content .= "</ul>\n";

        return $content;
    }
}

$rivendell_now_and_next = RivendellNowAndNext::instance();

