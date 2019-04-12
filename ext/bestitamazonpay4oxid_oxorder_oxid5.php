<?php

/**
 * Extension for OXID oxOrder model on OXID version < 6
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_oxOrder_oxid5 extends bestitAmazonPay4Oxid_oxOrder_oxid5_parent
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
     * Manages received user data from Amazon
     *
     * @param oxUser $oUser       Customers user object
     * @param object $oAmazonData User data received from Amazon WS
     *
     * @return oxUser
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    protected function _manageFullUserData($oUser, $oAmazonData)
    {
        $oContainer = $this->_getContainer();
        $oSession = $oContainer->getSession();
        $oConfig = $oContainer->getConfig();

        //Parse data from Amazon for OXID
        $aParsedData = $oContainer->getAddressUtil()->parseAmazonAddress(
            $oAmazonData->Destination->PhysicalDestination
        );

        $aDefaultMap = array(
            'oxcompany' => $aParsedData['CompanyName'],
            'oxfname' => $aParsedData['FirstName'],
            'oxlname' => $aParsedData['LastName'],
            'oxcity' => $aParsedData['City'],
            'oxstateid' => $aParsedData['StateOrRegion'],
            'oxcountryid' => $aParsedData['CountryId'],
            'oxzip' => $aParsedData['PostalCode'],
            'oxfon' => $aParsedData['Phone'],
            'oxstreet' => $aParsedData['Street'],
            'oxstreetnr' => $aParsedData['StreetNr'],
            'oxaddinfo' => $aParsedData['AddInfo']
        );

        //Getting Email
        $sEmail = $oAmazonData->Buyer->Email;

        // If we find user account in OXID with same email that we got from Amazon then add new shipping address
        $oDatabase = $oContainer->getDatabase();

        $sQuery = "SELECT OXID
            FROM oxuser
            WHERE OXUSERNAME = {$oDatabase->quote($sEmail)}
            AND OXSHOPID = {$oDatabase->quote($oConfig->getShopId())}";

        $sUserWithSuchEmailOxid = (string)$oDatabase->getOne($sQuery);

        if ($sUserWithSuchEmailOxid !== '') {
            //Load existing user from oxid
            $oUser->load($sUserWithSuchEmailOxid);

            /** @var oxAddress $oDelAddress */
            $oDelAddress = $oContainer->getObjectFactory()->createOxidObject('oxAddress');

            //Maybe we have already shipping address added for this user ? If yes then use it
            /**
             * @var oxAddress[] $aUserAddresses
             */
            $aUserAddresses = $oUser->getUserAddresses();

            foreach ($aUserAddresses as $oAddress) {
                if ((string)$oAddress->getFieldData('oxfname') === (string)$aParsedData['FirstName']
                    && (string)$oAddress->getFieldData('oxlname') === (string)$aParsedData['LastName']
                    && (string)$oAddress->getFieldData('oxstreet') === (string)$aParsedData['Street']
                    && (string)$oAddress->getFieldData('oxstreetnr') === (string)$aParsedData['StreetNr']
                ) {
                    $oDelAddress->load($oAddress->getId());
                    break;
                }
            }

            $oDelAddress->assign(array_merge($aDefaultMap, array('oxuserid' => $sUserWithSuchEmailOxid)));
            $sDeliveryAddressId = $oDelAddress->save();

            $oSession->setVariable('blshowshipaddress', 1);
            $oSession->setVariable('deladrid', $sDeliveryAddressId);
        } else {
            // If the user is new and not found in OXID update data from Amazon
            $oUser->assign(array_merge($aDefaultMap, array('oxusername' => $sEmail)));
            $oUser->save();

            $sDeliveryAddressId = (string) $oSession->getVariable('deladrid');

            // Set Amazon address as shipping
            if ($sDeliveryAddressId !== '') {
                /** @var oxAddress $oDelAddress */
                $oDelAddress = $oContainer->getObjectFactory()->createOxidObject('oxAddress');
                $oDelAddress->load($sDeliveryAddressId);
                $oDelAddress->assign($aDefaultMap);
                $oDelAddress->save();
            }
        }

        return $oUser;
    }

    /**
     * Calls Authorize method in Amazon depending on settings
     *
     * @param oxBasket $oBasket
     * @param string   $sAmazonOrderReferenceId
     * @param bool     $blOptimizedFlow
     *
     * @return bool
     * @throws Exception
     */
    protected function _callSyncAmazonAuthorize($oBasket, $sAmazonOrderReferenceId, $blOptimizedFlow)
    {
        $oContainer = $this->_getContainer();
        $oSession = $oContainer->getSession();
        $oUtils = $oContainer->getUtils();
        $oConfig = $oContainer->getConfig();

        //Authorize method call in Amazon
        $aParams = array(
            'amazon_order_reference_id' => $sAmazonOrderReferenceId,
            'authorization_amount' => $oBasket->getPrice()->getBruttoPrice(),
            'currency_code' => $oBasket->getBasketCurrency()->name,
            'authorization_reference_id' => $sAmazonOrderReferenceId.'_'
                .$oContainer->getUtilsDate()->getTime()
        );

        $oData = $oContainer->getClient()->authorize(null, $aParams, true);

        //Error handling
        if (!$oData || $oData->Error) {
            $oUtils->redirect($oConfig->getShopSecureHomeUrl() . 'cl=user&fnc=cleanAmazonPay', false);
            return false;
        }

        $oAuthorizationStatus = $oData->AuthorizeResult->AuthorizationDetails->AuthorizationStatus;

        //Response handling
        if ((string)$oAuthorizationStatus->State === 'Declined' || (string)$oAuthorizationStatus->State === 'Closed') {
            //Redirect to order page to re-select the payment
            if ($blOptimizedFlow === true && (string)$oAuthorizationStatus->ReasonCode === 'TransactionTimedOut') {
                return false;
            } elseif ((string)$oAuthorizationStatus->ReasonCode === 'InvalidPaymentMethod') {
                $oSession->setVariable('blAmazonSyncChangePayment', 1);
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=order&action=changePayment', false);
                return false;
            }

            //Cancel ORO in amazon and redirect
            $aParams['amazon_order_reference_id'] = $oSession->getVariable('amazonOrderReferenceId');
            $oContainer->getClient()->cancelOrderReference(null, $aParams);
            $oUtils->redirect(
                $oConfig->getShopSecureHomeUrl()
                    .'cl=user&fnc=cleanAmazonPay&error=BESTITAMAZONPAY_PAYMENT_DECLINED_OR_REJECTED',
                false
            );
            return false;
        }

        //Open Response handling
        if ((string)$oAuthorizationStatus->State === 'Open') {
            //Set response into session for later saving into order info
            $oSession->setVariable(
                'sAmazonSyncResponseState',
                (string)$oAuthorizationStatus->State
            );
            $oSession->setVariable(
                'sAmazonSyncResponseAuthorizationId',
                (string)$oData->AuthorizeResult->AuthorizationDetails->AmazonAuthorizationId
            );
        } else {
            //Unexpected behaviour
            $oUtils->redirect(
                $oConfig->getShopSecureHomeUrl() . 'cl=user&fnc=cleanAmazonPay',
                false
            );

            return false;
        }

        return true;
    }

    /**
     * @param oxBasket $oBasket
     * @param oxUser   $oUser
     * @param bool     $blIsAmazonOrder
     * @param bool     $blAuthorizeAsync
     *
     * @return bool
     * @throws Exception
     */
    protected function _preFinalizeOrder(oxBasket &$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
    {
        $blAuthorizeAsync = false;
        $oContainer = $this->_getContainer();
        $oSession = $oContainer->getSession();
        $oUtils = $oContainer->getUtils();
        $oConfig = $oContainer->getConfig();

        $blIsAmazonOrder = $oBasket->getPaymentId() === 'bestitamazon'
            && $oConfig->getRequestParameter('cl') === 'order';

        //Situation when amazonOrderReferenceId was wiped out somehow, do cleanup and redirect
        if ($blIsAmazonOrder === true) {
            $sAmazonOrderReferenceId = (string)$oSession->getVariable('amazonOrderReferenceId');

            if ($sAmazonOrderReferenceId === '') {
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay', false);
                return false;
            }

            $sBasketHash = $oContainer->getBasketUtil()->getBasketHash($sAmazonOrderReferenceId, $oBasket);
            $sCurrentBasketHash = $oConfig->getRequestParameter('amazonBasketHash');
            $sCurrentBasketHash = $sCurrentBasketHash === null ?
                $oSession->getVariable('sAmazonBasketHash') :
                $sCurrentBasketHash;

            if ($sCurrentBasketHash !== $sBasketHash) {
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay', false);
                return false;
            }

            //Get Full customer data from Amazon
            $oData = $oContainer->getClient()->getOrderReferenceDetails();
            $sStatus = (string)$oData->GetOrderReferenceDetailsResult
                ->OrderReferenceDetails->OrderReferenceStatus->State;

            if ($sStatus !== 'Open') {
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay', false);
                return false;
            }

            $blOptimizedFlow = (string)$oConfig->getConfigParam('sAmazonMode')
                === bestitAmazonPay4OxidClient::OPTIMIZED_FLOW;

            //Call Amazon authorize (Dedicated for Sync mode), don't call if ERP mode is enabled
            if ((bool)$oConfig->getConfigParam('blAmazonERP') !== true
                && $this->_callSyncAmazonAuthorize($oBasket, $sAmazonOrderReferenceId, $blOptimizedFlow) === false
            ) {
                if ($blOptimizedFlow === true) {
                    $blAuthorizeAsync = true;
                } else {
                    return false;
                }
            }

            //Manage full user data with user data updates
            $oAmazonData = $oData->GetOrderReferenceDetailsResult->OrderReferenceDetails;
            $oUser = $this->_manageFullUserData($oUser, $oAmazonData);
        }

        return true;
    }

    /**
     * Async Authorize call and data update
     *
     * @param bool $blAuthorizeAsync
     *
     * @throws Exception
     * @throws oxSystemComponentException
     */
    protected function _performAmazonActions($blAuthorizeAsync)
    {
        $oContainer = $this->_getContainer();
        $oSession = $oContainer->getSession();
        $oConfig = $oContainer->getConfig();

        //Save Amazon reference ID to oxorder table
        $this->_setFieldData('bestitamazonorderreferenceid', $oSession->getVariable('amazonOrderReferenceId'));
        $this->save();
        $oContainer->getClient()->setOrderAttributes($this);

        //If ERP mode is enabled do nothing just set oxorder->oxtransstatus to specified value
        if ((bool)$oConfig->getConfigParam('blAmazonERP') === true) {
            $this->_setFieldData('oxtransstatus', $oConfig->getConfigParam('sAmazonERPModeStatus'));
            $this->save();
            return;
        }

        //If we had Sync mode enabled don't call Authorize once again
        if ($blAuthorizeAsync === false) {
            $sAmazonSyncResponseState = (string)$oSession->getVariable('sAmazonSyncResponseState');
            $sAmazonSyncResponseAuthorizationId = (string)$oSession->getVariable('sAmazonSyncResponseAuthorizationId');

            if ($sAmazonSyncResponseState !== '' && $sAmazonSyncResponseAuthorizationId !== '') {
                $this->assign(array(
                    'bestitamazonauthorizationid' => $sAmazonSyncResponseAuthorizationId,
                    'oxtransstatus' => 'AMZ-Authorize-'.$sAmazonSyncResponseState
                ));
                $this->save();
            }

            //If Capture handling was set to "Direct Capture after Authorize" and Authorization status is Open
            if ((string)$oConfig->getConfigParam('sAmazonCapture') === 'DIRECT'
                && (string)$oSession->getVariable('sAmazonSyncResponseState') === 'Open'
            ) {
                $oContainer->getClient()->capture($this);
            }

            return;
        }

        //Call Amazon authorize (Dedicated for Async mode)
        $oContainer->getClient()->authorize($this);
        return;
    }

    /**
     * @param oxBasket $oBasket
     * @param oxUser   $oUser
     * @param bool     $blRecalculatingOrder
     *
     * @return int
     */
    protected function _parentFinalizeOrder(oxBasket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
    }

    /**
     * Confirm Order details to Amazon if payment id is bestitamazon and amazonreferenceid exists
     * Update user details with the full details received from amazon
     *
     * @param oxBasket   $oBasket
     * @param oxUser     $oUser
     * @param bool|false $blRecalculatingOrder
     *
     * @return int
     * @throws Exception
     */
    public function finalizeOrder(oxBasket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        if ($this->_preFinalizeOrder($oBasket, $oUser, $blIsAmazonOrder, $blAuthorizeAsync) === false) {
            return oxOrder::ORDER_STATE_PAYMENTERROR;
        }

        //Original OXID method which creates and order
        $iRet = $this->_parentFinalizeOrder($oBasket, $oUser, $blRecalculatingOrder);

        //If order was successfull perform some Amazon actions
        if ($blIsAmazonOrder === true) {
            //If order was successfull update order details with reference ID
            if ($iRet < 2) {
                $this->_performAmazonActions($blAuthorizeAsync);
            } else {
                $this->_getContainer()->getClient()->cancelOrderReference($this);
            }
        }

        return $iRet;
    }

    /**
     * Skips delivery address validation when payment==bestitamazon
     *
     * @param oxUser $oUser user object
     *
     * @return int
     * @throws oxSystemComponentException
     */
    public function validateDeliveryAddress($oUser)
    {
        $oBasket = $this->_getContainer()->getSession()->getBasket();

        if ($oBasket && (string)$oBasket->getPaymentId() === 'bestitamazon') {
            return 0;
        } else {
            return parent::validateDeliveryAddress($oUser);
        }
    }

    /**
     * Method returns payment method change link for Invalid payment method order
     *
     * @return string
     * @throws Exception
     */
    public function getAmazonChangePaymentLink()
    {
        $oClient = $this->_getContainer()->getClient();
        //Main part of the link related to selected locale in config
        $sLink = $oClient->getAmazonProperty('sAmazonPayChangeLink', true);

        //Send GetOrderReferenceDetails request to Amazon to get OrderLanguage string
        $oData = $oClient->getOrderReferenceDetails($this, array(), true);

        //If we have language string add it to link
        if (!empty($oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderLanguage)) {
            $sAmazonLanguageString = (string)$oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderLanguage;
            $sLink .= str_replace('-', '_', $sAmazonLanguageString);
        }

        return $sLink;
    }
}