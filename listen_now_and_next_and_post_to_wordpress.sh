#!/bin/bash

##### CHANGE VARIABLES BELOW

# "UDP port" where Rivendell sends its now & next messages
PORT=2345

# public URL of your wordpress install
WORDPRESS_BASE_URL="http://localhost/"

# secret key, as set in the plugin's parameters
KEY="sôm&ethi'ng w\\\"eird((--\$))"




####### DO NOT EDIT BELOW ####################

# TODO grab this with netcat listening on UDP
ARTISTTITLE="Hahaha test with ' & âccents___Tït\\\"le"

# protect & from URL-encoding
ARTISTTITLE=${ARTISTTITLE/\&/\%26}
KEY=${KEY/\&/\%26}

URL="${WORDPRESS_BASE_URL}wp/wp-admin/admin-post.php?action=rivendell_now_and_next_store"

curl -X POST -d "key=$KEY" -d "artisttitle=$ARTISTTITLE" $URL
