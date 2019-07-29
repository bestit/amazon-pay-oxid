<?php

use Psr\Log\LoggerInterface;

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
     * The logger
     *
     * @var LoggerInterface
     */
    protected $_oLogger;

    /**
     * Get the logger
     *
     * @return LoggerInterface
     */
    protected function _getLogger()
    {
        if ($this->_oLogger=== null) {
            $this->_oLogger = $this->_getContainer()->getLogger();
        }

        return $this->_oLogger;
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

        $this->_getLogger()->debug(
            'Managed full amazon user data',
            array('email' => $sEmail)
        );

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

            $this->_getLogger()->debug(
                'Existing user found',
                array('email' => $sEmail, 'parsedAddress' => $aParsedData)
            );

            foreach ($aUserAddresses as $oAddress) {
                if ((string)$oAddress->getFieldData('oxfname') === (string)$aParsedData['FirstName']
                    && (string)$oAddress->getFieldData('oxlname') === (string)$aParsedData['LastName']
                    && (string)$oAddress->getFieldData('oxstreet') === (string)$aParsedData['Street']
                    && (string)$oAddress->getFieldData('oxstreetnr') === (string)$aParsedData['StreetNr']
                ) {
                    $this->_getLogger()->debug(
                        'Existing shipping address found, use this address'
                    );

                    $oDelAddress->load($oAddress->getId());
                    break;
                }
            }

            $oDelAddress->assign(array_merge($aDefaultMap, array('oxuserid' => $sUserWithSuchEmailOxid)));
            $sDeliveryAddressId = $oDelAddress->save();

            $oSession->setVariable('blshowshipaddress', 1);
            $oSession->setVariable('deladrid', $sDeliveryAddressId);
        } else {
            $this->_getLogger()->debug(
                'new user found, create new user entity',
                array('mappedData' => $aDefaultMap)
            );

            // If the user is new and not found in OXID update data from Amazon
            $oUser->assign(array_merge($aDefaultMap, array('oxusername' => $sEmail)));
            $oUser->save();

            $sDeliveryAddressId = (string) $oSession->getVariable('deladrid');

            // Set Amazon address as shipping
            if ($sDeliveryAddressId !== '') {
                $this->_getLogger()->debug(
                    'Set amazon address as shipping address'
                );

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

        $this->_getLogger()->debug(
            'Call amazon authorize in sync mode'
        );

        $oData = $oContainer->getClient()->authorize(null, $aParams, true);

        //Error handling
        if (!$oData || $oData->Error) {
            $this->_getLogger()->error(
                'Redirect after authorize error'
            );

            $oUtils->redirect($oConfig->getShopSecureHomeUrl() . 'cl=user&fnc=cleanAmazonPay', false);
            return false;
        }

        $oAuthorizationStatus = $oData->AuthorizeResult->AuthorizationDetails->AuthorizationStatus;

        //Response handling
        if ((string)$oAuthorizationStatus->State === 'Declined' || (string)$oAuthorizationStatus->State === 'Closed') {
            //Redirect to order page to re-select the payment
            $this->_getLogger()->error(
                'Closed/declined authorization detected',
                array('reason' => (string) $oAuthorizationStatus->ReasonCode)
            );

            if ($blOptimizedFlow === true && (string)$oAuthorizationStatus->ReasonCode === 'TransactionTimedOut') {
                $this->_getLogger()->debug(
                    'optimized flow detected and reason timeout detected'
                );
                return false;
            } elseif ((string)$oAuthorizationStatus->ReasonCode === 'InvalidPaymentMethod') {
                $this->_getLogger()->debug(
                    'invalid payment method detected, redirect to payment page for re-select payment'
                );
                $oSession->setVariable('blAmazonSyncChangePayment', 1);
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=order&action=changePayment', false);
                return false;
            }

            $this->_getLogger()->debug(
                'Cancel order reference and redirect to error page'
            );

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

            $this->_getLogger()->debug(
                'Open authorization detected'
            );
        } else {
            $this->_getLogger()->debug(
                'Unexpected behaviour detected, redirect to user page and clean up'
            );
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

        $this->_getLogger()->debug(
            'Process pre finalize process of the order creation',
            array('async' => $blAuthorizeAsync, 'isAmazonOrder' => $blIsAmazonOrder)
        );

        //Situation when amazonOrderReferenceId was wiped out somehow, do cleanup and redirect
        if ($blIsAmazonOrder === true) {
            $sAmazonOrderReferenceId = (string)$oSession->getVariable('amazonOrderReferenceId');

            if ($sAmazonOrderReferenceId === '') {
                $this->_getLogger()->error(
                    'No reference id found, cleanup and redirect'
                );

                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay', false);
                return false;
            }

            $sBasketHash = $oContainer->getBasketUtil()->getBasketHash($sAmazonOrderReferenceId, $oBasket);
            $sCurrentBasketHash = $oConfig->getRequestParameter('amazonBasketHash');
            $sCurrentBasketHash = $sCurrentBasketHash === null ?
                $oSession->getVariable('sAmazonBasketHash') :
                $sCurrentBasketHash;

            if ($sCurrentBasketHash !== $sBasketHash) {
                $this->_getLogger()->error(
                    'Invalid basket hash detected, cleanup and redirect'
                );
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay', false);
                return false;
            }

            //Get Full customer data from Amazon
            $oData = $oContainer->getClient()->getOrderReferenceDetails();
            $sStatus = (string)$oData->GetOrderReferenceDetailsResult
                ->OrderReferenceDetails->OrderReferenceStatus->State;

            if ($sStatus !== 'Open') {
                $this->_getLogger()->error(
                    'Oro state not open, cleanup and redirect'
                );
                $oUtils->redirect($oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay', false);
                return false;
            }

            $blOptimizedFlow = (string)$oConfig->getConfigParam('sAmazonMode')
                === bestitAmazonPay4OxidClient::OPTIMIZED_FLOW;

            $this->_getLogger()->debug(
                'Decide if sync authorize should be called',
                array('erpMode' => $erpMode = (bool)$oConfig->getConfigParam('blAmazonERP'))
            );

            //Call Amazon authorize (Dedicated for Sync mode), don't call if ERP mode is enabled
            if ($erpMode !== true
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
     *
     * @return void
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

        $this->_getLogger()->debug(
            'Perform amazon actions'
        );

        //If ERP mode is enabled do nothing just set oxorder->oxtransstatus to specified value
        if ((bool)$oConfig->getConfigParam('blAmazonERP') === true) {
            $this->_getLogger()->debug(
                'ERP mode detected, set order state',
                array('state' => $erpStatus = $oConfig->getConfigParam('sAmazonERPModeStatus'))
            );
            $this->_setFieldData('oxtransstatus', $erpStatus);
            $this->save();
            return;
        }

        //If we had Sync mode enabled don't call Authorize once again
        if ($blAuthorizeAsync === false) {
            $this->_getLogger()->debug(
                'Sync mode enabled, dont call authorize again'
            );
            $sAmazonSyncResponseState = (string)$oSession->getVariable('sAmazonSyncResponseState');
            $sAmazonSyncResponseAuthorizationId = (string)$oSession->getVariable('sAmazonSyncResponseAuthorizationId');

            if ($sAmazonSyncResponseState !== '' && $sAmazonSyncResponseAuthorizationId !== '') {
                $this->assign($orderData = array(
                    'bestitamazonauthorizationid' => $sAmazonSyncResponseAuthorizationId,
                    'oxtransstatus' => 'AMZ-Authorize-'.$sAmazonSyncResponseState
                ));

                $this->_getLogger()->debug(
                    'Set order state from session variable',
                    $orderData
                );

                $this->save();
            }

            //If Capture handling was set to "Direct Capture after Authorize" and Authorization status is Open
            if ((string)$oConfig->getConfigParam('sAmazonCapture') === 'DIRECT'
                && (string)$oSession->getVariable('sAmazonSyncResponseState') === 'Open'
            ) {
                $this->_getLogger()->debug(
                    'Capture order by direct capture option'
                );
                $oContainer->getClient()->capture($this);
            }

            $this->_getLogger()->debug(
                'Amazon actions process finished'
            );

            return;
        }

        $this->_getLogger()->debug(
            'Async mode enabled, call authorize again'
        );

        //Call Amazon authorize (Dedicated for Async mode)
        $oContainer->getClient()->authorize($this);
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
            $this->_getLogger()->debug(
                'Amazon order by oxid created',
                array('response' => $iRet)
            );

            //If order was successfull update order details with reference ID
            if ($iRet < 2) {
                $this->_performAmazonActions($blAuthorizeAsync);
            } else {
                $this->_getLogger()->error(
                    'Error at order creation detected'
                );
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
