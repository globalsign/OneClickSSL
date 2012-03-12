<?php
/**
 * OneClickPlugin
 *
 * @category
 * @package
 * @copyright Copyright (c) 2012 SessionDigital. (http://www.sessiondigital.com)
 * @author Rupert Jones <rupert@sessiondigital.com>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface OneClickSSLPlugin
{
    public function setDomain($domain);

    public function setOutput(Output_Output $output);

    public function install($privateKey, $certificate, $cacert = null);

    /**
     * The following methods do not 100% have to be implemented
     */

    /**
    public function checkIp();
     */

    /**
    public function backup();
     */

    /**
    public function restoreBackup();
     */
}
