#!/bin/bash
clear

echo "################################################################"
echo "#                                                              #"
echo "# Uninstalling GlobalSign OneClickSSL(TM) Plugin for cPanel(R) #"
echo "#                                                              #"
echo "################################################################"

sleep 2
/usr/local/cpanel/bin/unregister_cpanelplugin /usr/local/cpanel/3rdparty/OneClickSSL/oneclickssl.cpanelplugin
/usr/local/cpanel/bin/unregister_cpanelplugin /usr/local/cpanel/3rdparty/OneClickSSL/settings.cpanelplugin
/usr/local/cpanel/bin/unregister_cpanelplugin /usr/local/cpanel/3rdparty/OneClickSSL/revocation.cpanelplugin

sleep 2

rm -vRf /usr/local/cpanel/3rdparty/OneClickSSL
rm -vRf /usr/local/cpanel/base/frontend/x3/OneClickSSL
rm -vf /usr/local/cpanel/whostmgr/docroot/cgi/addon_OneClickSSL.cgi
rm -vRf /usr/local/cpanel/whostmgr/docroot/3rdparty/OneClickSSL

