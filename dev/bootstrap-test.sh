#!/bin/sh

SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BASE_DIR=$(dirname "$SCRIPT_DIR");

echo "Don't forget to add .test TLD into your local DNS server\n";

# this function is called when Ctrl-C is sent
function trap_ctrlc ()
{
    # perform cleanup here
    echo "Ctrl-C caught..."
    echo "Stopping PHP Server: $PHP_PID"
    echo "Stopping Selenium: $SELENIUM_PID"
    exec 2> /dev/null
    kill $PHP_PID $SELENIUM_PID
    echo "Stopping HTTP Server"
    nginx -c $SCRIPT_DIR/nginx.conf -p $SCRIPT_DIR -s stop
}

wget -q -O $SCRIPT_DIR/selenium.jar http://selenium-release.storage.googleapis.com/3.8/selenium-server-standalone-3.8.1.jar --no-clobber

trap "trap_ctrlc" 2

php $BASE_DIR/bin/server --cookie-lifetime=5 127.0.0.1:8888 > /dev/null 2>&1 & PHP_PID=$!
java -jar $SCRIPT_DIR/selenium.jar > /dev/null 2>&1 & SELENIUM_PID=$!
nginx -c $SCRIPT_DIR/nginx.conf -p $SCRIPT_DIR

echo "Selenium process ID: $SELENIUM_PID"
echo "PHP server process ID: $PHP_PID"

wait
