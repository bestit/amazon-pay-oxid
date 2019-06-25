<?php

$sVendorAutoloader = realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';

if (file_exists($sVendorAutoloader) === true) {
    include_once realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';
}

use Monolog\Logger;

/**
 * Controller for IPN handling
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
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
