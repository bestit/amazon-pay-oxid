<?php

/**
 * Main model to provide information
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid extends bestitAmazonPay4OxidContainer
{
    /**
     * @var bool
     */
    protected $_isSelectedCurrencyAvailable = null;

    /**
     * @var bool
     */
    protected $_blActive = null;

    /**
     * Returns true if currency meets locale
     *
     * @return boolean
     */
    public function getIsSelectedCurrencyAvailable()
    {
        $oConfig = $this->getConfig();
        $blEnableMultiCurrency = (bool)$oConfig->getConfigParam('blBestitAmazonPay4OxidEnableMultiCurrency');

        $this->getLogger()->debug('Check if selected currency is available');

        if ($blEnableMultiCurrency === true) {
            $this->getLogger()->debug('Multi Currency enabled');

            return true;
        }

        if ($this->_isSelectedCurrencyAvailable === null) {
            $this->_isSelectedCurrencyAvailable = true;

            $aMap = array(
                'DE' => 'EUR',
                'UK' => 'GBP',
                'US' => 'USD'
            );
            $sLocale = (string)$oConfig->getConfigParam('sAmazonLocale');
            $sCurrency = (string)$this->getSession()->getBasket()->getBasketCurrency()->name;

            //If Locale is DE and currency is not EURO don't allow Amazon checkout process
            if (isset($aMap[$sLocale]) && $aMap[$sLocale] !== $sCurrency) {
                $this->getLogger()->debug(
                    'Currency for amazon pay is not available',
                    array('sLocale' => $sLocale, 'sCurrency' => $sCurrency)
                );
                $this->_isSelectedCurrencyAvailable = false;
            }
        }

        return $this->_isSelectedCurrencyAvailable;
    }

    /**
     * Method checks if Amazon Pay is active and can be used
     *
     * @return bool
     * @throws oxConnectionException
     */
    public function isActive()
    {
        $this->getLogger()->debug('Check if amazon is active');

        //If check was made once return result
        if ($this->_blActive !== null) {
            $this->getLogger()->debug('Return cached active result', array('result' => $this->_blActive));
            return $this->_blActive;
        }

        //Check if payment method itself is active
        $sTable = getViewName('oxpayments');
        $sSql = "SELECT OXACTIVE
            FROM {$sTable}
            WHERE OXID = 'bestitamazon'";

        $blPaymentActive = (bool)$this->getDatabase()->getOne($sSql);

        if ($blPaymentActive === false) {
            $this->getLogger()->debug('Payment not active');
            return $this->_blActive = false;
        }

        //Check if payment has at least one shipping method assigned
        $sO2PTable = getViewName('oxobject2payment');
        $sDelSetTable = getViewName('oxdeliveryset');
        $sSql = "SELECT OXOBJECTID
            FROM {$sO2PTable} AS o2p RIGHT JOIN {$sDelSetTable} AS d 
              ON (o2p.OXOBJECTID = d.OXID AND d.OXACTIVE = 1)
            WHERE OXPAYMENTID = 'bestitamazon'
              AND OXTYPE='oxdelset'
            LIMIT 1";

        $sShippingId = (string)$this->getDatabase()->getOne($sSql);

        if ($sShippingId === '') {
            $this->getLogger()->debug('Assigned shipment method missing or invalid', array('sShippingId' => $sShippingId));
            return $this->_blActive = false;
        }

        //Check if shipping method has at least one shipping cost assigned
        $sTable = getViewName('oxdel2delset');
        $sSql = "SELECT OXID 
            FROM {$sTable} 
            WHERE OXDELSETID = {$this->getDatabase()->quote($sShippingId)}
            LIMIT 1";
        $sShippingCostRelated = (string)$this->getDatabase()->getOne($sSql);

        if ($sShippingCostRelated === '') {
            $this->getLogger()->debug(
                'Assigned shipment cost missing or invalid',
                array('sShippingCostRelated' => $sShippingCostRelated)
            );
            return $this->_blActive = false;
        }

        //Check if selected currency is available for selected Amazon locale
        if ($this->getIsSelectedCurrencyAvailable() === false) {
            return $this->_blActive = false;
        }

        $oConfig = $this->getConfig();

        //If Amazon SellerId is empty
        if ((string)$oConfig->getConfigParam('sAmazonSellerId') === '') {
            $this->getLogger()->debug('Amazon seller id is empty');
            return $this->_blActive = false;
        }

        //If basket items price = 0
        $sClass = (string)$oConfig->getRequestParameter('cl');

        if ($sClass !== 'details'
            && $sClass !== 'oxwarticledetails'
            && (float)$this->getSession()->getBasket()->getPrice()->getBruttoPrice() === 0.0
        ) {
            $this->getLogger()->debug('Basket brutto price is 0');
            return $this->_blActive = false;
        }

        return $this->_blActive = true;
    }

    /**
     * Cleans Amazon pay as the selected one, including all related variables, records and values
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function cleanAmazonPay()
    {
        //Delete our created user for Amazon checkout
        $oUser = $this->getActiveUser();
        $sAmazonUserName = $this->getSession()->getVariable('amazonOrderReferenceId') . '@amazon.com';

        $this->getLogger()->debug('Clean amazon pay environment for the current user');

        if ($oUser !== false && $oUser->getFieldData('oxusername') === $sAmazonUserName) {
            $oUser->delete();
            $this->getLogger()->debug('Delete user', array('id' => $oUser->getId()));
        }

        //Delete several session variables to clean up Amazon data in session
        $this->getSession()->deleteVariable('amazonOrderReferenceId');
        $this->getSession()->deleteVariable('sAmazonSyncResponseState');
        $this->getSession()->deleteVariable('sAmazonSyncResponseAuthorizationId');
        $this->getSession()->deleteVariable('blAmazonSyncChangePayment');
        $this->getSession()->deleteVariable('sAmazonBasketHash');

        //General cleanup of user accounts that has been created for orders and wos not used
        $this->cleanUpUnusedAccounts();
    }

    /**
     * Deletes previously created user accounts which was not used
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function cleanUpUnusedAccounts()
    {
        $sTable = getViewName('oxuser');
        $sSql = "SELECT oxid, oxusername
            FROM {$sTable}
            WHERE oxusername LIKE '%-%-%@amazon.com'
              AND oxcreate < (NOW() - INTERVAL 1440 MINUTE)";

        $aData = $this->getDatabase()->getAll($sSql);

        $this->getLogger()->debug('cleanup unused Accounts', array('ids' => array_column($aData, 'oxid')));

        foreach ($aData as $aUser) {
            //Delete user from OXID
            $oUser = $this->getObjectFactory()->createOxidObject('oxUser');

            if ($oUser->load($aUser['oxid'])) {
                $oUser->delete();
                $this->getLogger()->debug('Delete user', array('id' => $oUser->getId()));
            }
        }
    }
}
