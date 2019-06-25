<?php

/**
 * Extension for OXID thankyou controller
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_thankyou extends bestitAmazonPay4Oxid_thankyou_parent
{
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
     * Restore the basket if necessary.
     *
     * @throws oxSystemComponentException
     */
    public function init()
    {
        $this->_parentInit();

        $this->_getContainer()->getBasketUtil()->restoreQuickCheckoutBasket();
    }

    /**
     * Delete Amazon pay details after checkout completed
     *
     * @return mixed
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    public function render()
    {
        $this->_getContainer()->getModule()->cleanAmazonPay();
        return parent::render();
    }

    /**
     * Init the parent
     */
    protected function _parentInit()
    {
        parent::init();
    }
}
