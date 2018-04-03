<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitamazonpay4oxidobjectfactory.php
 *
 * The bestitAmazonPay4OxidObjectFactory class file.
 *
 * PHP versions 5
 *
 * @category  bestitAmazonPay4Oxid
 * @package   bestitAmazonPay4Oxid
 * @author    best it GmbH & Co. KG - Alexander Schneider <schneider@bestit-online.de>
 * @copyright 2017 best it GmbH & Co. KG
 * @version   GIT: $Id$
 * @link      http://www.bestit-online.de
 */

$sVendorAutoloader = realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';

if (file_exists($sVendorAutoloader) === true) {
    require_once(realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php');
}

use AmazonPay\IpnHandler;

/**
 * Class bestitAmazonPay4OxidObjectFactory
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
