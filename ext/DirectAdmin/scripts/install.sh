#!/bin/sh

cd $DOCUMENT_ROOT; //this directory
cd ..

if [ ! -f etc/oneclick.conf ];
then
	/bin/cp etc/oneclick.conf_default etc/oneclick.conf
fi

/bin/chmod 755 -R $DOCUMENT_ROOT/../
/bin/chown diradmin:diradmin -R $DOCUMENT_ROOT/../

/bin/mkdir tmp

/bin/chmod 777 -R tmp
/bin/chmod 777 -R etc

/bin/chmod 644 plugin.conf
/bin/chmod 644 hooks/*
/bin/chmod 644 images/*

/bin/echo '<p>GlobalSign OneClickSSL Plugin Installed!</p>';

/bin/echo '<p><a href="http://www.globalsign.com/ssl/oneclickssl/directadmin/" target="_blank"> Click here to visit our website for more information and support</a></p>';

/bin/echo '<p><strong><a href="/CMD_PLUGINS_ADMIN/OneClickSSL">Please check the configuration of the plugin now!</a></strong></p>';

exit 0;
