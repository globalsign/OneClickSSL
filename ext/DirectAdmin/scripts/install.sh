#!/bin/sh

cd $DOCUMENT_ROOT; //this directory
cd ..

if [ ! -f etc/oneclick.conf ];
then
	cp etc/oneclick.conf_default etc/oneclick.conf
fi

chmod 755 -R $DOCUMENT_ROOT/../
chown diradmin:diradmin -R $DOCUMENT_ROOT/../

mkdir tmp

chmod 777 -R tmp
chmod 777 -R etc

chmod 644 plugin.conf
chmod 644 hooks/*
chmod 644 images/*

echo '<p>GlobalSign OneClickSSL Plugin Installed!</p>';

echo '<p><a href="http://www.globalsign.com/ssl/oneclickssl/directadmin/" target="_blank"> Click here to visit our website for more information and support</a></p>';

echo '<p><strong><a href="/CMD_PLUGINS_ADMIN/OneClickSSL">Please check the configuration of the plugin now!</a></strong></p>';

exit 0;
