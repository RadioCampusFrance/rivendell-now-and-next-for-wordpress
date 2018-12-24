=== Rivendell Now&Next collector and browser ===
Contributors: kirchgem
Tags: podcast, audio, feed, radio, media
Requires at least: 4.9
Tested up to: 4.9
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Collects "Now & Next" signals from RDAirPlay (Rivendell's automation software),
stores the playlist and lets the user browse the past playlist.

Creates a "playlist" webpage, which you can edit to change the title and the
header text.

TODO
* listener script as a system service
* i18n https://codex.wordpress.org/I18n_for_WordPress_Developers
* let the user browse the playlist by hour/date
* drop table in https://developer.wordpress.org/reference/functions/register_uninstall_hook/
* table cleanup (keep the history for only N months, add it as a parameter)

== Installation ==

On the playing host:
* launch RDAdmin
* "Manage hosts"
* select your player
* click the "RDAirplay" button
* click the "Configure Now & Next parameters" button
* In the "Master log" (at the top) section, enter the IP of the transmitter :
  the machine running listen_now_and_next_and_post_to_wordpress.sh. The UDP port
  number should match the PORT written in this file.
  In "UDP string" enter "%a___%t%R" (that's 3 underscores). Click OK to close all
  this and restart RDAirplay on this machine.

On the listener (assuming this is a freshly installed CentOS7) :
* sudo yum install nmap-ncat
* sudo firewall-cmd --permanent --zone=public --add-port=2345/udp
* sudo firewall-cmd --reload

Copy the plug-in forlder in your Wordpress installation, to /wp/wp-content/plugins.
Log in Wordpress as an admin and go to the extensions page
Find "Rivendell Now&Next collector and browser" in the list, and click on "activate".
In the left menu browse to "Settings" and "Rivendell".
Set a secret key: choose something long.

The last step is the customisation of listen_now_and_next_and_post_to_wordpress.sh,
change at least WORDPRESS_BASE_URL and KEY, according to what you set above.

Then copy this file to the listener machine, and run it with 
"nohup ./listen_now_and_next_and_post_to_wordpress.sh &> listen_now_and_next_and_post_to_wordpress.log &"

Tracks should start to appear on http://yourWordpress.site/playlist 
