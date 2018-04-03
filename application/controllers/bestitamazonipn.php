<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitamazonipn.php
 *
 * The bestitamazonipn class file.
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

use Monolog\Logger;

/**
 * Class bestitAmazonIpn
 */
class bestitAmazonIpn extends oxUBase
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'bestitamazonpay4oxidcron.tpl';

    /**
     * @var string
     */
    protected $_sInput = 'php://input';

    /**
     * @var null|bestitAmazonPay4OxidContainer
     */
    protected $_oContainer = null;

    /**
     * Returns the active user object.
     *
     * @return bestitAmazonPay4OxidContainer
     * @throws oxSystemComponentException
     */
    protected function _getContainer()
    {
        if ($this->_oContainer === null) {
            $this->_oContainer = oxNew('bestitAmazonPay4OxidContainer');
        }

        return $this->_oContainer;
    }

    /**
     * @param string $sError
     *
     * @return string
     * @throws oxSystemComponentException
     * @throws Exception
     */
    protected function _processError($sError)
    {
        $this->_getContainer()->getIpnHandler()->logIPNResponse(Logger::ERROR, $sError);
        $this->setViewData(array('sError' => $sError));
        return $this->_sThisTemplate;
    }

    /**
     * The controller entry point.
     *
     * @return string
     * @throws Exception
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function render()
    {
        //If ERP mode is enabled do nothing, if IPN or CRON authorize unauthorized orders
        if ($this->_getContainer()->getConfig()->getConfigParam('blAmazonERP') === true) {
            return $this->_processError('IPN response handling disabled - ERP mode is ON (Module settings)');
        }

        //Check if IPN response handling is turned ON
        if ($this->_getContainer()->getConfig()->getConfigParam('sAmazonAuthorize') !== 'IPN') {
            return $this->_processError('IPN response handling disabled (Module settings)');
        }

        //Get SNS message
        $sBody = file_get_contents($this->_sInput);

        if ($sBody === '') {
            return $this->_processError('SNS message empty or Error while reading SNS message occurred');
        }

        //Perform IPN action
        if ($this->_getContainer()->getIpnHandler()->processIPNAction($sBody) !== true) {
            return $this->_processError('Error while handling Amazon response');
        }

        return $this->_sThisTemplate;
    }
}
