<?php

use Psr\Log\LoggerInterface;

/**
 * Extension for OXID order_overview controller
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_order_overview extends bestitAmazonPay4Oxid_order_overview_parent
{
    /**
     * @var null|bestitAmazonPay4OxidContainer
     */
    protected $_oContainer = null;

    /**
     * The logger
     *
     * @var LoggerInterface
     */
    protected $_oLogger;

    /**
     * bestitAmazonPay4Oxid_order_overview constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oLogger = $this->_getContainer()->getLogger();
    }

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
     * Capture order after changing it to shipped
     * @throws Exception
     */
    public function sendorder()
    {
        parent::sendorder();
        /** @var oxOrder $oOrder */
        $oOrder = $this->_getContainer()->getObjectFactory()->createOxidObject('oxOrder');

        if ($oOrder->load($this->getEditObjectId()) === true
            && $oOrder->getFieldData('oxPaymentType') === 'bestitamazon'
        ) {
            $this->_oLogger->debug(
                'Save amazon pay capture for order',
                array('orderNumber' => $oOrder->getFieldData('oxordernr'))
            );
            $this->_getContainer()->getClient()->saveCapture($oOrder);
        }
    }
}
