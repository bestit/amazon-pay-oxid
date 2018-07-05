<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitamazonpay4oxid_oxorder.php
 *
 * The bestitAmazonPay4Oxid_oxorder class file.
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

use \OxidEsales\Eshop\Application\Model\Basket;

/**
 * Class bestitAmazonPay4Oxid_oxOrder
 */
class bestitAmazonPay4Oxid_oxOrder extends bestitAmazonPay4Oxid_oxOrder_parent
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
            'oxstreetnr' => $aParsedData['StreetNr']
        );

        //Getting Email
        $sEmail = $oAmazonData->Buyer->Email;
        $sDeliveryAddressId = (string) $oSession->getVariable('deladrid');

        //If we have logged in user already and Amazon address set as shipping
        if ($sDeliveryAddressId !== '') {
            /** @var oxAddress $oDelAddress */
            $oDelAddress = $oContainer->getObjectFactory()->createOxidObject('oxAddress');
            $oDelAddress->load($sDeliveryAddressId);

            $oDelAddress->assign(array_merge($aDefaultMap, array('oxaddinfo' => $aParsedData['AddInfo'])));
            $oDelAddress->save();

            return $oUser;
        }

        // If we don't have user logged in but we have found user account in OXID with same email
        // that came from Amazon, then add new shipping address and log user in
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
                    && (string)$oAddress->getFieldData('oxstreetnr') === (string)$aParsedData['Street']
                ) {
                    $oDelAddress->load($oAddress->getId());
                    break;
                }
            }

            $oDelAddress->assign(array_merge($aDefaultMap, array('oxuserid' => $sUserWithSuchEmailOxid)));
            $sDeliveryAddressId = $oDelAddress->save();

            $oSession->setVariable('blshowshipaddress', 1);
            $oSession->setVariable('deladrid', $sDeliveryAddressId);

            return $oUser;
        }

        //If the user is new and not found in OXID update data from Amazon and log user in
        $oUser->assign(array_merge($aDefaultMap, array('oxusername' => $sEmail)));
        $oUser->save();

        return $oUser;
    }

    /**
     * Calls Authorize method in Amazon depending on settings
     *
     * @param Basket $oBasket
     * @param string $sAmazonOrderReferenceId
     * @param bool   $blOptimizedFlow
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
     * @param Basket $oBasket
     * @param oxUser $oUser
     * @param bool   $blIsAmazonOrder
     * @param bool   $blAuthorizeAsync
     *
     * @return bool
     * @throws Exception
     */
    protected function _preFinalizeOrder(Basket &$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
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

            //Confirm Order Reference and Manage user data
            $oData = $oContainer->getClient()->confirmOrderReference();

            if (!$oData || $oData->Error) {
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
     * @param Basket $oBasket
     * @param User   $oUser
     * @param bool   $blRecalculatingOrder
     *
     * @return int
     */
    protected function _parentFinalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
    {
        return parent::finalizeOrder($oBasket, $oUser, $blRecalculatingOrder);
    }

    /**
     * Confirm Order details to Amazon if payment id is bestitamazon and amazonreferenceid exists
     * Update user details with the full details received from amazon
     *
     * @param Basket     $oBasket
     * @param User       $oUser
     * @param bool|false $blRecalculatingOrder
     *
     * @return int
     * @throws Exception
     */
    public function finalizeOrder(Basket $oBasket, $oUser, $blRecalculatingOrder = false)
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