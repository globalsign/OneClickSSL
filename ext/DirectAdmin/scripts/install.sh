#!/bin/sh
OS=`uname`

if [ -z $DOCUMENT_ROOT ]
then
	export DOCUMENT_ROOT='/usr/local/directadmin/plugins/OneClickSSL/scripts'
fi

cd $DOCUMENT_ROOT
cd ..

if [ ! -f etc/oneclick.conf ];
then
	/bin/cp etc/oneclick.conf_default etc/oneclick.conf
fi

if [ "$OS" = "FreeBSD" ]; then
        CHOWN=/usr/sbin/chown
else
        CHOWN=/bin/chown
fi

$CHOWN -R diradmin:diradmin $DOCUMENT_ROOT/../
/bin/chmod -R 755 $DOCUMENT_ROOT/../

/bin/mkdir -p tmp

/bin/chmod -R 777 tmp
/bin/chmod -R 777 etc

/bin/chmod 644 plugin.conf
/bin/chmod 644 hooks/*
/bin/chmod 644 images/*

/bin/echo '<p>GlobalSign OneClickSSL Plugin Installed!</p>';
/bin/echo '<p><a href="http://www.globalsign.com/ssl/oneclickssl/directadmin/" target="_blank"> Click here to visit our website for more information and support</a></p>';
/bin/echo '<p><strong><a href="/CMD_PLUGINS_ADMIN/OneClickSSL">Please check the configuration of the plugin now!</a></strong></p>';

exit 0;
