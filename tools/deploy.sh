#!/bin/bash
shopt -s extglob

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd $DIR/../

echo "$1"

if [ "$1" = "all-deps" ]; then
    for file in `find . -name "*.php" -type f`; do php -l "$file" || exit 1; done
    scp -r public/* public/.[^.]* vps_web:/web/stdktv/htdocs/
    scp -r includes/!(*.local.*) vps_web:/web/stdktv/includes/
elif [ "$1" = "css" ]; then
    for file in `find public/css -name "*.php" -type f`; do php -l "$file" || exit 1; done
    scp -r public/css vps_web:/web/stdktv/htdocs/
elif [ "$1" = "js" ]; then
    for file in `find public/js -name "*.php" -type f`; do php -l "$file" || exit 1; done
    scp -r public/js vps_web:/web/stdktv/htdocs/
elif [ "$1" = "assets" ]; then
    for file in `find public/{css,js} -name "*.php" -type f`; do php -l "$file" || exit 1; done
    scp -r public/css public/js vps_web:/web/stdktv/htdocs/
elif [ "$1" = "pages" ]; then
    for file in `find public -type f -name "*.php" -not -path "*/vendor/*"`; do php -l "$file" || exit 1; done
    pages=`find public -name "*.php" -or -name "*.html" -or -name "*.htm" -or -name "*.cgi" -or -name "*.pl" -or -name "*.py" -or -name "*.rb" -or -name "*.rhtml"`
    scp $pages vps_web:/web/stdktv/htdocs/
elif [ "$1" = "includes" ]; then
    for file in `find includes -name "*.php" -type f`; do php -l "$file" || exit 1; done
    scp -r includes/!(*.local.*) vps_web:/web/stdktv/includes/
elif [ "$1" = "all" -o "$1" = "original" ]; then
    for file in `find . -type f -name "*.php" -not -path "*/vendor/*"`; do php -l "$file" || exit 1; done
    scp -r public/!(js|css|*.local.*) vps_web:/web/stdktv/htdocs/
    scp -r public/js/!(vendor) vps_web:/web/stdktv/htdocs/js/
    scp -r public/css/!(vendor) vps_web:/web/stdktv/htdocs/css/
    scp -r includes/!(*.local.*) vps_web:/web/stdktv/includes/
elif [ -f "$1" ]; then
    filename=${file##*/}
    path=${file#*/stdktv/}
    path=${path%/*}
    echo "Would upload $filename to $path"
else
    echo "Did not understand."
fi
