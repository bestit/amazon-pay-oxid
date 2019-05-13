<?php

/**
 * Extension for OXID order controller
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_order extends bestitAmazonPay4Oxid_order_parent
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
     * @param string $sError
     * @param string $sRedirectUrl
     *
     * @throws oxSystemComponentException
     */
    protected function _setErrorAndRedirect($sError, $sRedirectUrl)
    {
        /** @var oxUserException $oEx */
        $oEx = $this->_getContainer()->getObjectFactory()->createOxidObject('oxUserException');
        $oEx->setMessage($sError);
        $this->_getContainer()->getUtilsView()->addErrorToDisplay($oEx, false, true);
        $this->_getContainer()->getUtils()->redirect($sRedirectUrl, false);
    }

    /**
     * Returns the amazon billing address.
     *
     * @return array|null
     * @throws Exception
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function getAmazonBillingAddress()
    {
        $oOrderReferenceDetails = $this->_getContainer()->getClient()->getOrderReferenceDetails();
        $oDetails = $oOrderReferenceDetails->GetOrderReferenceDetailsResult->OrderReferenceDetails;

        if (isset($oDetails->BillingAddress) === true) {
            $aParsedData = $this->_getContainer()
                ->getAddressUtil()
                ->parseAmazonAddress($oDetails->BillingAddress->PhysicalAddress);

            return array(
                'oxfname' => $aParsedData['FirstName'],
                'oxlname' => $aParsedData['LastName'],
                'oxcity' => $aParsedData['City'],
                'oxstateid' => $aParsedData['StateOrRegion'],
                'oxcountryid' => $aParsedData['CountryId'],
                'oxzip' => $aParsedData['PostalCode'],
                'oxstreet' => $aParsedData['Street'],
                'oxstreetnr' => $aParsedData['StreetNr'],
                'oxaddinfo' => $aParsedData['AddInfo'],
                'oxcompany' => $aParsedData['CompanyName']
            );
        }

        return null;
    }

    /**
     * Returns the county name for the current billing address country.
     *
     * @param string $sCountryId
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function getCountryName($sCountryId)
    {
        /** @var oxCountryList $oCountryList */
        $oCountry = $this->_getContainer()->getObjectFactory()->createOxidObject('oxCountry');

        return ($oCountry->load($sCountryId) === true) ? (string) $oCountry->getFieldData('oxTitle') : '';
    }

    /**
     * Updates the user data with the amazon data.
     * @throws Exception
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function updateUserWithAmazonData()
    {
        $billingAddress = $this->getAmazonBillingAddress();

        if ($billingAddress !== null) {
            $oUser = $this->getUser();
            $oUser->assign($billingAddress);
            $oUser->save();
        }
    }

    /**
     * Sets the basket hash.
     *
     * @throws oxSystemComponentException
     */
    protected function setBasketHash()
    {
        $oContainer = $this->_getContainer();
        $sBasketHash = $oContainer->getConfig()->getRequestParameter('amazonBasketHash');

        if ($sBasketHash) {
            $oContainer->getSession()->setVariable('sAmazonBasketHash', $sBasketHash);
        }
    }

    /**
     * The main render function with additional payment checks.
     *
     * @return mixed
     *
     * @throws Exception
     * @throws oxSystemComponentException
     */
    public function render()
    {
        $sTemplate = parent::render();
        $oPayment = $this->getPayment();
        $oConfig = $this->_getContainer()->getConfig();

        //payment is set and not oxempty if amazon selected?
        if ($oPayment !== false) {
            $sPaymentId = (string)$this->getPayment()->getId();
            $sAmazonOrderReferenceId = (string)$this->_getContainer()
                ->getSession()->getVariable('amazonOrderReferenceId');

            if ($sAmazonOrderReferenceId !== '') {
                if ($sPaymentId === 'oxempty') {
                    $this->_setErrorAndRedirect(
                        'BESTITAMAZONPAY_NO_PAYMENTS_FOR_SHIPPING_ADDRESS',
                        $oConfig->getShopSecureHomeUrl().'cl=user'
                    );

                    return $sTemplate;
                } elseif ($sPaymentId === 'bestitamazon'
                    && (string)$oConfig->getRequestParameter('fnc') !== 'execute'
                    && (string)$oConfig->getRequestParameter('action') !== 'changePayment'
                ) {
                    //Send Order reference details to Amazon if payment id is bestitamazon and amazonreferenceid exists
                    //Send SetOrderReferenceDetails request
                    $oData = $this->_getContainer()->getClient()->setOrderReferenceDetails($this->getBasket());
                    $oReferenceDetails = isset($oData->SetOrderReferenceDetailsResult->OrderReferenceDetails)
                        ? $oData->SetOrderReferenceDetailsResult->OrderReferenceDetails : null;

                    //If payment method is not valid to choose
                    if ($oReferenceDetails !== null
                        && (string)$oReferenceDetails->Constraints->Constraint->ConstraintID === 'PaymentMethodNotAllowed'
                    ) {
                        $this->_setErrorAndRedirect(
                            'BESTITAMAZONPAY_CHANGE_PAYMENT',
                            $oConfig->getShopSecureHomeUrl().'cl=payment'
                        );
                        return $sTemplate;
                    }

                    //If there's some other unexpected error
                    if ($oReferenceDetails === null
                        || (string)$oReferenceDetails->OrderReferenceStatus->State !== 'Draft'
                    ) {
                        $sAdditionalParameters = '';

                        // check if there is any information about an error
                        if ($oData->Error->Code) {
                            $sAdditionalParameters = '&bestitAmazonPay4OxidErrorCode='.$oData->Error->Code
                                .'&error='.$oData->Error->Message;
                        }

                        $this->_getContainer()->getUtils()->redirect(
                            $oConfig->getShopSecureHomeUrl().'cl=user&fnc=cleanAmazonPay'.$sAdditionalParameters,
                            false
                        );
                        return $sTemplate;
                    }
                }
            } elseif ($sPaymentId === 'bestitamazon') {
                // If selected payment was bestitamazon but there's no amazonreferenceid,
                // redirect back to second step and show message
                $this->_setErrorAndRedirect(
                    'BESTITAMAZONPAY_CHANGE_PAYMENT',
                    $oConfig->getShopSecureHomeUrl().'cl=basket'
                );
            }
        }

        $this->setBasketHash();

        return $sTemplate;
    }

    /**
     * Renders the json data.
     *
     * @param string $sData The json data.
     *
     * @throws oxSystemComponentException
     */
    protected function renderJson($sData)
    {
        header('Content-Type: application/json');
        $this->setBasketHash();
        echo $sData;
        exit;
    }

    /**
     * @return void
     * @throws oxSystemComponentException
     * @throws Exception
     */
    public function confirmAmazonOrderReference()
    {
        $success = false;
        $oContainer = $this->_getContainer();
        $oConfig = $oContainer->getConfig();
        $oSession = $oContainer->getSession();
        $sSecureUrl = $oConfig->getShopSecureHomeUrl();
        $sFailureUrl = $sSecureUrl . 'cl=user&fnc=processAmazonCallback&cancelOrderReference=1';

        if ($oSession->checkSessionChallenge()) {
            $oBasket = $oSession->getBasket();
            $blIsAmazonOrder = $oBasket->getPaymentId() === 'bestitamazon'
                && $oConfig->getRequestParameter('cl') === 'order';

            //Situation when amazonOrderReferenceId was wiped out somehow, do cleanup and redirect
            if ($blIsAmazonOrder === true) {
                $sAmazonOrderReferenceId = (string)$oSession->getVariable('amazonOrderReferenceId');

                if ($sAmazonOrderReferenceId !== '') {
                    $sSuccessUrl = $sSecureUrl . html_entity_decode($oConfig->getRequestParameter('formData'))
                        . '&amazonBasketHash=' . $oContainer->getBasketUtil()->getBasketHash(
                            $sAmazonOrderReferenceId,
                            $oBasket
                        );

                    //Confirm Order Reference
                    $oData = $oContainer->getClient()->confirmOrderReference(array(
                        'success_url' => $sSuccessUrl,
                        'failure_url' => $sFailureUrl
                    ));

                    if ($oData && !$oData->Error) {
                        $success = true;
                    }
                }
            }
        }

        $this->renderJson(json_encode(array(
            'success' => $success,
            'redirectUrl' => $sFailureUrl
        )));
    }
}