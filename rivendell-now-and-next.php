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
        'name' => _x( 'Playlist entries', 'playlist_entry' ),
        'singular_name' => _x( 'Playlist entry', 'playlist_entry' ),
        'add_new' => _x( 'Add New', 'playlist_entry' ),
        'add_new_item' => _x( 'Add New Playlist entry', 'playlist_entry' ),
        'edit_item' => _x( 'Edit Playlist entry', 'playlist_entry' ),
        'new_item' => _x( 'New Playlist entry', 'playlist_entry' ),
        'view_item' => _x( 'View Playlist entry', 'playlist_entry' ),
        'search_items' => _x( 'Search Playlist entries', 'playlist_entry' ),
        'not_found' => _x( 'No Playlist entries found', 'playlist_entry' ),
        'not_found_in_trash' => _x( 'No Playlist entries found in Trash', 'playlist_entry' ),
        'parent_item_colon' => _x( 'Parent Playlist entry:', 'playlist_entry' ),
        'menu_name' => _x( 'Playlist entries', 'playlist_entry' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Music reviews filterable by genre',
        'supports' => array( 'title' ),
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
}
 
add_action( 'init', 'register_cpt_playlist_entry' );
