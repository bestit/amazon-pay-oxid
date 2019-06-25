<?php

$sVendorAutoloader = realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';

if (file_exists($sVendorAutoloader) === true) {
    include_once realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';
}

use AmazonPay\IpnHandler;

/**
 * Factory for this Amazon Pay module
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidObjectFactory
{
    /**
     * Returns a new object instance.
     *
     * @param string $sClass
     *
     * @return object
     * @throws oxSystemComponentException
     */
    public function createOxidObject($sClass)
    {
        return oxNew($sClass);
    }

    /**
     * @param array  $aHeaders
     * @param string $sBody
     *
     * @return IpnHandler
     */
    public function createIpnHandler(array $aHeaders, $sBody)
    {
        return new IpnHandler($aHeaders, $sBody);
    }
}
