#!/bin/bash

##### CHANGE VARIABLES BELOW

# public URL of your wordpress install - don't forget the / at the end
WORDPRESS_BASE_URL="http://localhost/"

# secret key, as set in the plugin's parameters
KEY="sôm&ethi'ng w\\\"eird((--\$))"

# "UDP port" where Rivendell sends its now & next messages
PORT=2345




####### DO NOT EDIT BELOW ####################

if ! which ncat &> /dev/null
then
    echo "Exiting: this script requires ncat (on Centos, install with sudo yum install nmap-ncat)";
    exit 1;
fi

function post_to_wordpress() {
    # protect & from URL-encoding
    ARTISTTITLE=${1/\&/\%26}
    KEY=${KEY/\&/\%26}

    URL="${WORDPRESS_BASE_URL}wp/wp-admin/admin-post.php?action=rivendell_now_and_next_store"

    curl -s -S -X POST -d "key=$KEY" -d "artisttitle=$ARTISTTITLE" $URL > /dev/null
}

while true
do
	line=`ncat -u -l $PORT -i 1s 2> /dev/null`
	if [ ! -z "$line" ];
	then
		post_to_wordpress "$line";
	fi
done;


