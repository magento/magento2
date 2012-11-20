#!/bin/sh

function logprint() {
    echo "`date`:  $1"
}

if [ -z $1 ]; then
    logprint 'Please specify directory where builds should be placed.'
    exit 1
fi

BUILD_SCOPE="$1"
BUILDPATH=/home/qa/builds
MINBUILDCOUNT=10
MYSQLUSER="$2"
MYSQLPASS="$3"


MMDBBUILDPACKAGES=`ls -1 $BUILDPATH | grep "^$BUILD_SCOPE"`
DELETECOUNT=`expr $MINBUILDCOUNT + 1`

logprint "Build cleaning started"
for package in $MMDBBUILDPACKAGES; do
    NEWBUILDS=`find $BUILDPATH/$package -maxdepth 1 -mtime -$MINBUILDCOUNT -type d | grep -v "^$BUILDPATH/$package$"`
    NEWBUILDSCOUNT=`find $BUILDPATH/$package -maxdepth 1 -mtime -$MINBUILDCOUNT -type d | grep -v "^$BUILDPATH/$package$" | wc -l`
    logprint "  Processing $package build package"
    if [ $NEWBUILDSCOUNT -ge $MINBUILDCOUNT ]; then
        BUILDSTODELETE=`find $BUILDPATH/$package -maxdepth 1 -mtime +$MINBUILDCOUNT -type d | grep -v "^$BUILDPATH/$package$" | sort -n`
    else
        BUILDSCOUNT=`find $BUILDPATH/$package -maxdepth 1 -type d | grep -v "^$BUILDPATH/$package$" | wc -l`
        if [ $BUILDSCOUNT -gt $MINBUILDCOUNT ]; then
            FILTERCOUNT=`expr $BUILDSCOUNT - $MINBUILDCOUNT`
            BUILDSTODELETE=`find $BUILDPATH/$package -maxdepth 1 -type d | grep -v "^$BUILDPATH/$package$" | sort -n | head -n $FILTERCOUNT`
        else
           BUILDSTODELETE=""
        fi
    fi

    if [[ ${#BUILDSTODELETE[@]} -eq 0 || $BUILDSTODELETE = '' ]]; then
        logprint "    Nothing to do"
    fi

    for build in $BUILDSTODELETE; do
        DATABASE=`grep -R dbname $build/app/etc/ | grep "local.xml:" | grep mysql | head -n1 | cut -d "[" -f 3 | cut -d "]" -f 1`
        logprint "    Cleaning $build"
        if [ $DATABASE ]; then
            logprint "      Dropping $DATABASE"
            `mysql -u$MYSQLUSER -p$MYSQLPASS -e "drop database $DATABASE"`
        fi
        logprint "      Removing files from $build"
        `rm -rf $build`
    done
done
