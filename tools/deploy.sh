#!/bin/bash
shopt -s extglob

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR/../

if [ "$1" = "all-deps" ]; then
    scp -r public/* public/.htaccess vps_web:/web/stdktv/htdocs/
elif [ "$1" = "css" ]; then
    scp -r public/css/ vps_web:/web/stdktv/htdocs/
elif [ "$1" = "js" ]; then
    scp -r public/js/ vps_web:/web/stdktv/htdocs/
elif [ "$1" = "assets" ]; then
    scp -r public/css/ public/js/ vps_web:/web/stdktv/htdocs/
elif [ "$1" = "pages" ]; then
    scp -r public/*.{php,html,htm,cgi,pl} vps_web:/web/stdktv/htdocs/
elif [ "$1" = "all" -o "$1" = "original" ]; then
    scp -r public/!(js|css) public/js/!(vendor) public/css/!(vendor) vps_web:/web/stdktv/htdocs/
fi
