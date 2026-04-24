=== Playlist recorder and display page ===
Contributors: kirchgem
Tags: podcast, audio, feed, radio, media
Requires at least: 4.9
Tested up to: 5.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plug-in:
* Collects "artist - title" events coming from external HTTP POSTs
* Creates a `/playlist` Wordpress page (which you can edit to change the title and the
header text) where the user can browse the playlist.

TODO
----

* drop table in https://developer.wordpress.org/reference/functions/register_uninstall_hook/

Installation
============

On the WordPress server:
* Copy the plug-in folder in your Wordpress installation (to `/wp/wp-content/plugins`).
* Log in Wordpress as an admin and go to the extensions page.
* Find "Playlist recorder and display page" in the list, and click on "activate".
* In the left menu browse to "Settings" and "Playlist".
* Set a secret key: choose something long.

On the player, find a way to make a HTTP POST each time a new song plays, the URL is
`http://yourWordpress.site/wp/wp-admin/admin-post.php?action=playlist_store`.
The query should contain the following form data fields:
   - `key` (matching the secret key set in settings)
   - `artist` (non-empty)
   - `title` (non-empty)

Tracks should start to appear on http://yourWordpress.site/playlist 

CSS tip:  # TIP: in CSS you can select artist with "li.rivendell-playlist span:nth-of-type(2)" 
