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

/**
 * Register the custom post type "Playlist entry"
 */
function register_cpt_playlist_entry() {

    $labels = array(
        'name' => __( 'Playlist entries', 'rivendell-now-and-next' ),
        'singular_name' => __( 'Playlist entry', 'rivendell-now-and-next' ),
        'add_new' => __( 'Add New', 'rivendell-now-and-next' ),
        'add_new_item' => __( 'Add New Playlist entry', 'rivendell-now-and-next' ),
        'edit_item' => __( 'Edit Playlist entry', 'rivendell-now-and-next' ),
        'new_item' => __( 'New Playlist entry', 'rivendell-now-and-next' ),
        'view_item' => __( 'View Playlist entry', 'rivendell-now-and-next' ),
        'search_items' => __( 'Search Playlist entries', 'rivendell-now-and-next' ),
        'not_found' => __( 'No Playlist entries found', 'rivendell-now-and-next' ),
        'not_found_in_trash' => __( 'No Playlist entries found in Trash', 'rivendell-now-and-next' ),
        'parent_item_colon' => __( 'Parent Playlist entry:', 'rivendell-now-and-next' ),
        'menu_name' => __( 'Playlist entries', 'rivendell-now-and-next' ),
    );

    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => "Rivendell's playlist",
        'supports' => array('title'),
        'taxonomies' => array(),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-format-audio',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'playlist_entry', $args );
    //TODO load_plugin_textdomain('rivendell-now-and-next', false, basename( dirname( __FILE__ ) ) . '/lang' 
}

add_action( 'init', 'register_cpt_playlist_entry' );
add_option('key');
