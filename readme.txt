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
* i18n https://codex.wordpress.org/I18n_for_WordPress_Developers
* drop table in https://developer.wordpress.org/reference/functions/register_uninstall_hook/

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

ncat -u -l 2345


