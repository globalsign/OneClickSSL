#!/bin/bash

if [ -n "$1" ]
then
    if [ -d "/tmp/OneClickSSL" ]
    then
        rm -rf /tmp/OneClickSSL
    fi

    mkdir /tmp/OneClickSSL

    cp -r ../ext/DirectAdmin/* /tmp/OneClickSSL

    cp ../lib/*.php /tmp/OneClickSSL/lib
    cp -r ../lib/Output /tmp/OneClickSSL/lib/Output
    cp -r ../lib/Images /tmp/OneClickSSL/images
    cp -r ../lib/Languages/* /tmp/OneClickSSL/etc
    cd /tmp/OneClickSSL
    tar czf $1 ./

    rm -rf /tmp/OneClickSSL*

    echo "Done"
else
    echo "Usage: makeDirectAdminPlugin.sh <pluginFile>"
fi
