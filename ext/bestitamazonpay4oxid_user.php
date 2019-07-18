<?php

/**
 * Extension for OXID user controller
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_user extends bestitAmazonPay4Oxid_user_parent
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
     * Set Amazon reference ID to session
     *
     * @return mixed
     * @throws oxSystemComponentException
     */
    public function render()
    {
        $sOrderReferenceId = $this->_getContainer()->getConfig()->getRequestParameter('amazonOrderReferenceId');

        if ($sOrderReferenceId) {
            $this->_getContainer()->getSession()->setVariable('amazonOrderReferenceId', $sOrderReferenceId);
        }

        return parent::render();
    }
}
