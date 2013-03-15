#!/bin/bash

if [ -n "$1" ]
then
    if [ -d "/tmp/OneClickSSL" ]
    then
        rm -rf /tmp/OneClickSSL
    fi

    mkdir /tmp/OneClickSSL

    cp -Xvr ../ext/cPanel/* /tmp/OneClickSSL

    cp -Xvr ../lib/*.php /tmp/OneClickSSL/3rdparty/OneClickSSL/lib/
    cp -Xvr ../lib/Output /tmp/OneClickSSL/3rdparty/OneClickSSL/lib/
    cp -Xvr ../lib/Languages /tmp/OneClickSSL/3rdparty/OneClickSSL/lib/
    cp -Xvr ../lib/Images /tmp/OneClickSSL/base/frontend/x3/OneClickSSL/images
    
    cd /tmp/OneClickSSL
    tar --exclude=" .*" -czf $1 ./

    rm -rf /tmp/OneClickSSL*

    echo "Done"
else
    echo "Usage: makecPanelPlugin.sh <pluginFile>"
fi
