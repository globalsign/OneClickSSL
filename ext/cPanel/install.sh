#!/bin/bash
clear

echo "################################################################"
echo "#                                                              #"
echo "# Installing GlobalSign OneClickSSL(TM) Plugin for cPanel(R)   #"
echo "#                                                              #"
echo "################################################################"

sleep 2

cp -vRf ./base /usr/local/cpanel/
cp -vRf ./3rdparty /usr/local/cpanel/
cp -vRf ./whostmgr /usr/local/cpanel/

chmod v +x /usr/local/cpanel/whostmgr/docroot/cgi/addon_OneClickSSL.cgi
chmod -vR 777 /usr/local/cpanel/3rdparty/OneClickSSL/tmp
chmod -vR 777 /usr/local/cpanel/3rdparty/OneClickSSL/etc

sleep 2

/usr/local/cpanel/bin/register_cpanelplugin /usr/local/cpanel/3rdparty/OneClickSSL/oneclickssl.cpanelplugin
/usr/local/cpanel/bin/register_cpanelplugin /usr/local/cpanel/3rdparty/OneClickSSL/settings.cpanelplugin
/usr/local/cpanel/bin/register_cpanelplugin /usr/local/cpanel/3rdparty/OneClickSSL/revocation.cpanelplugin

