<?php

/**
 * Extension for OXID oxDeliverySetList model
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_oxDeliverySetList extends bestitAmazonPay4Oxid_oxDeliverySetList_parent
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
     * Returns if Amazon pay is assigned available shipping ways
     *
     * @param string $sShipSet the string to quote
     *
     * @return boolean
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    protected function _getShippingAvailableForPayment($sShipSet)
    {
        $oDatabase = $this->_getContainer()->getDatabase();

        $sSql = "SELECT OXOBJECTID
            FROM oxobject2payment
            WHERE OXOBJECTID = {$oDatabase->quote($sShipSet)}
                AND OXPAYMENTID = {$oDatabase->quote('bestitamazon')}
                AND OXTYPE = 'oxdelset'
                AND OXTYPE = 'oxdelset' LIMIT 1";

        $sShippingId = $oDatabase->getOne($sSql);
        return ($sShippingId) ? true : false;
    }

    /**
     * If Amazon pay was selected remove other payment options and leave only Amazon pay
     * If Amazon pay was selected remove shipping options where amazon pay is not assigned
     *
     * Loads deliveryset data, checks if it has payments assigned. If active delivery set id
     * is passed - checks if it can be used, if not - takes first ship set id from list which
     * fits. For active ship set collects payment list info. Retuns array containing:
     *   1. all ship sets that has payment (array)
     *   2. active ship set id (string)
     *   3. payment list for active ship set (array)
     *
     * @param array    $aResult
     * @param oxUser   $oUser
     * @param oxBasket $oBasket
     *
     * @return mixed
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    protected function _processResult($aResult, $oUser, $oBasket)
    {
        $oConfig = $this->_getContainer()->getConfig();
        $sClass = $oConfig->getRequestParameter('cl');
        $sAmazonOrderReferenceId = $this->_getContainer()->getSession()->getVariable('amazonOrderReferenceId');
        $logger = $this->_getContainer()->getLogger();

        $logger->debug(
            'Process delivery set result',
            array('orderReferenceId' => $sAmazonOrderReferenceId)
        );

        //If Amazon Pay cannot be selected remove it from payments list
        if ($sClass === 'payment') {
            if ($this->_getContainer()->getModule()->isActive() !== true) {
                $logger->debug(
                    'Amazon pay not active, remove it'
                );
                unset($aResult[2]['bestitamazon']);
                return $aResult;
            }

            //If Amazon pay was selected with the button before leave only bestitamazon as payment selection
            if ($oUser !== false
                && isset($aResult[2]['bestitamazon'])
                && $sAmazonOrderReferenceId !== null
            ) {
                //If Amazon pay was selected remove other payment options and leave only Amazon pay
                $aResult[2] = array('bestitamazon' => $aResult[2]['bestitamazon']);

                $logger->debug(
                    'Amazon pay has been selected by button, remove other payment methods'
                );

                //If Amazon pay was selected remove shipping options where Amazon pay is not assigned
                foreach ($aResult[0] as $sKey => $sValue) {
                    if ($this->_getShippingAvailableForPayment($sKey) !== true) {
                        unset($aResult[0][$sKey]);
                    }
                }
            }
        }

        //If Amazon pay was not selected within the button click in 1st, 2nd step of checkout
        //check if selected currency is available for selected Amazon locale, if not remove amazon pay option from payments
        if ($sAmazonOrderReferenceId === null
            && ($this->_getContainer()->getModule()->getIsSelectedCurrencyAvailable() === false
                || $oBasket->getPrice()->getBruttoPrice() === 0
                || ((bool)$oConfig->getConfigParam('blAmazonLoginActive') === true
                    && $this->_getContainer()->getLoginClient()->showAmazonPayButton() === false)
            )
        ) {
            $logger->debug(
                'Payment not selected via button is step 1 or 2 and currency is not available for current local, remove it'
            );

            unset($aResult[2]['bestitamazon']);
        }

        return $aResult;
    }

    /**
     * Returns the delivery set data.
     *
     *
     * @param string   $sShipSet current ship set id (can be null if not set yet)
     * @param oxUser   $oUser    active user
     * @param oxBasket $oBasket  basket object
     *
     * @return array
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function getDeliverySetData($sShipSet, $oUser, $oBasket)
    {
        //Get $aActSets, $sActShipSet, $aActPaymentList in array from parent method
        $aResult = parent::getDeliverySetData($sShipSet, $oUser, $oBasket);
        return $this->_processResult($aResult, $oUser, $oBasket);
    }
}
