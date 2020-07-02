#!/bin/sh
# vim: set filetype=sh :

# Author: <Renato Mefi gh@mefi.in> https://github.com/renatomefi
# The original code lives in https://github.com/renatomefi/php-fpm-healthcheck
#
# A POSIX compliant shell script to healthcheck PHP fpm status, can be used only for pinging the status page
# or check for specific metrics
#
# i.e.: ./php-fpm-healthcheck --verbose --active-processes=6
# The script will fail in case the 'active processes' is bigger than 6.
#
# You can combine multiple options as well, the first one to fail will fail the healthcheck
# i.e.: ./php-fpm-healthcheck --listen-queue-len=10 --active-processes=6
#
# Ping mode (exit 0 if php-fpm returned data): ./php-fpm-healthcheck
#
# Ping mode with data (outputs php-fpm status text): ./php-fpm-healthcheck -v
#
# Exit status codes:
# 2,9,111 - Couldn't connect to PHP fpm, is it running?
# 8 - Couldn't reach PHP fpm status page, have you configured it with `pm.status_path = /status`?
# 1 - A healthcheck condition has failed
# 3 - Invalid option given
# 4 - One or more required softwares are missing
#
# Available options:
# -v|--verbose
# 
# Metric options, fails in case the CURRENT VALUE is bigger than the GIVEN VALUE
# --accepted-conn=n
# --listen-queue=n
# --max-listen-queue=n
# --idle-processes=n
# --active-processes=n
# --total-processes=n
# --max-active-processes=n
# --max-children-reached=n
# --slow-requests=n
#

set -eu

OPTIND=1 # Reset getopt in case it has been used previously in the shell

# Required software
FCGI_CMD_PATH=$(command -v cgi-fcgi) || { >&2 echo "Make sure fcgi is installed (i.e. apk add --no-cache fcgi). Aborting."; exit 4; }
command -v sed 1> /dev/null || { >&2 echo "Make sure sed is installed (i.e. apk add --no-cache busybox). Aborting."; exit 4; }
command -v tail 1> /dev/null || { >&2 echo "Make sure tail is installed (i.e. apk add --no-cache busybox). Aborting."; exit 4; }
command -v grep 1> /dev/null || { >&2 echo "Make sure grep is installed (i.e. apk add --no-cache grep). Aborting."; exit 4; }

# Get status from fastcgi connection
# $1 - cgi-fcgi connect argument
get_fpm_status() {
    if test "$VERBOSE" = 1; then printf "Trying to connect to php-fpm via: %s%s\\n" "$1" "$SCRIPT_NAME"; fi;
    
    # Since I cannot use pipefail I'll just split these in two commands
    FPM_STATUS=$(env -i REQUEST_METHOD="$REQUEST_METHOD" SCRIPT_NAME="$SCRIPT_NAME" SCRIPT_FILENAME="$SCRIPT_FILENAME" "$FCGI_CMD_PATH" -bind -connect "$1" 2> /dev/null)
    FPM_STATUS=$(echo "$FPM_STATUS" | tail +5)

    if test "$VERBOSE" = 1; then printf "php-fpm status output:\\n%s\\n" "$FPM_STATUS"; fi;

    if test "$FPM_STATUS" = "File not found."; then
        >&2 printf "php-fpm status page non reachable\\n";
        exit 8;
    fi;
}

# $1 - fpm option
# $2 - expected value threshold
check_fpm_health_by() {
    OPTION=$(echo "$1" | sed 's/--//g; s/-/ /g;')
    VALUE_EXPECTED="$2";
    VALUE_ACTUAL=$(echo "$FPM_STATUS" | grep "^$OPTION:" | cut -d: -f2 | sed 's/ //g')

    if test "$VERBOSE" = 1; then printf "'%s' value '%s' and expected is less than '%s'\\n" "$OPTION" "$VALUE_ACTUAL" "$VALUE_EXPECTED"; fi;

    if test "$VALUE_ACTUAL" -gt "$VALUE_EXPECTED"; then
        >&2 printf "'%s' value '%s' is greater than expected '%s'\\n" "$OPTION" "$VALUE_ACTUAL" "$VALUE_EXPECTED";
        exit 1;
    fi;
}

PARAM_AMOUNT=0

# $1 - fpm option
# $2 - expected value threshold
check_later() {
    # The POSIX sh way to check if it's an integer, also the output is supressed since it's polution
    if ! test "$2" -eq "$2" 2> /dev/null; then
        >&2 printf "'%s' option value must be an integer, '%s' given\\n" "$1" "$2"; exit 3;
    fi

    PARAM_AMOUNT=$(( PARAM_AMOUNT + 1 ))

    eval "PARAM_TO_CHECK$PARAM_AMOUNT=$1"
    eval "VALUE_TO_CHECK$PARAM_AMOUNT=$2"
}

# From the PARAM_TO_CHECK/VALUE_TO_CHECK magic variables, do all the checks
check_fpm_health() {
    j=1
    while [ $j -le $PARAM_AMOUNT ]; do
        eval "CURRENT_PARAM=\$PARAM_TO_CHECK$j"
        eval "CURRENT_VALUE=\$VALUE_TO_CHECK$j"
        check_fpm_health_by "$CURRENT_PARAM" "$CURRENT_VALUE"
        j=$(( j + 1 ))
    done
}

if ! GETOPT=$(getopt -o v --long verbose,accepted-conn:,listen-queue:,max-listen-queue:,listen-queue-len:,idle-processes:,active-processes:,total-processes:,max-active-processes:,max-children-reached:,slow-requests: -n 'php-fpm-healthcheck' -- "$@"); then
    >&2 echo "Invalid options, terminating." ; exit 3
fi;

eval set -- "$GETOPT"

# FastCGI variables
FCGI_CONNECT_DEFAULT="localhost:9000"
FCGI_STATUS_PATH_DEFAULT="/status"

export REQUEST_METHOD="GET"
export SCRIPT_NAME="${FCGI_STATUS_PATH:-$FCGI_STATUS_PATH_DEFAULT}"
export SCRIPT_FILENAME="${FCGI_STATUS_PATH:-$FCGI_STATUS_PATH_DEFAULT}"
FCGI_CONNECT="${FCGI_CONNECT:-$FCGI_CONNECT_DEFAULT}"

VERBOSE=0

while test "$1"; do
    case "$1" in
        -v|--verbose ) VERBOSE=1; shift ;;
        --) shift ; break ;;
        * ) check_later "$1" "$2"; shift 2 ;;
    esac
done

FPM_STATUS=false

get_fpm_status "$FCGI_CONNECT"
check_fpm_health
