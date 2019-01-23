=== Rivendell Now&Next collector and browser ===
Contributors: kirchgem
Tags: podcast, audio, feed, radio, media
Requires at least: 4.9
Tested up to: 4.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plug-in:
* Collects "Now & Next" signals from RDAirPlay (Rivendell's automation software)
* Creates a `/playlist` Wordpress page (which you can edit to change the title and the
header text) where the user can browse Rivendell's playlist.

We have 3 roles for the machines involved:
* a player, the one running RDAirplay, will send now & next signals via UDP to the transmitter.
* a transmitter, which runs our `listen_now_and_next_and_post_to_wordpress.sh` script. This machine can be the player.
* a wordpress server, which will receive artist/title signals from the transmitter.

TODO
----

* transmitter script as a system service
* i18n https://codex.wordpress.org/I18n_for_WordPress_Developers
* drop table in https://developer.wordpress.org/reference/functions/register_uninstall_hook/

Installation
============

On the player:
* launch RDAdmin
* click `Manage hosts`
* select your player
* click the `RDAirplay` button
* click the `Configure Now & Next parameters` button
* In the `Master log` (at the top) section, enter the IP of the transmitter. The UDP port
  number should match the `PORT` written in the transmitter script.
* In `UDP string` enter `%a___%t%R` (that's 3 underscores)
* Click OK to close all this
* Restart RDAirplay on the player.

On the WordPress server:
* Copy the plug-in forlder in your Wordpress installation (to `/wp/wp-content/plugins`).
* Log in Wordpress as an admin and go to the extensions page.
* Find "Rivendell Now&Next collector and browser" in the list, and click on "activate".
* In the left menu browse to "Settings" and "Rivendell".
* Set a secret key: choose something long.

On the transmitter (assuming this is a freshly installed CentOS7, where we listen on port 2345):
* `sudo yum install nmap-ncat`
* `sudo firewall-cmd --permanent --zone=public --add-port=2345/udp`
* `sudo firewall-cmd --reload`
*  change `listen_now_and_next_and_post_to_wordpress.sh`:
change at least `WORDPRESS_BASE_URL` and `KEY`, according to what you set above.
* copy this file to the transmitter machine, log in and run the script with 
`./listen_now_and_next_and_post_to_wordpress.sh &> listen_now_and_next_and_post_to_wordpress.log &`
* type `disown %1` before logging out

Tracks should start to appear on http://yourWordpress.site/playlist 
