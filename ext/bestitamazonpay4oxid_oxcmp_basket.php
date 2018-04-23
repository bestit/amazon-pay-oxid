<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitamazonpay4oxid_oxcmp_basket.php
 *
 * The bestitAmazonPay4Oxid_oxcmp_basket class file.
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

/**
 * Class bestitAmazonPay4Oxid_oxcmp_basket
 */
class bestitAmazonPay4Oxid_oxcmp_basket extends bestitAmazonPay4Oxid_oxcmp_basket_parent
{
    const BESTITAMAZONPAY_ERROR_CURRENCY_UNSUPPORTED = 'BESTITAMAZONPAY_ERROR_CURRENCY_UNSUPPORTED';
    const BESTITAMAZONPAY_ERROR_AMAZON_TERMINATED = 'BESTITAMAZONPAY_ERROR_AMAZON_TERMINATED';

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
     * Cleans Amazon pay as the selected one, including all related variables and values
     *
     * @param bool $cancelOrderReference
     *
     * @throws Exception
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function cleanAmazonPay($cancelOrderReference = false)
    {
        if ($cancelOrderReference === true) {
            $this->_getContainer()->getClient()->cancelOrderReference(
                null,
                array('amazon_order_reference_id' => $this->_getContainer()
                    ->getSession()
                    ->getVariable('amazonOrderReferenceId')
                )
            );
        }

        //Clean all related variables with user data and amazon reference id
        $this->_getContainer()->getModule()->cleanAmazonPay();
        $oConfig = $this->_getContainer()->getConfig();

        $sErrorCode = (string)$oConfig->getRequestParameter('bestitAmazonPay4OxidErrorCode');
        $sErrorMessage = (string)$oConfig->getRequestParameter('error');

        if ($sErrorCode === 'CurrencyUnsupported') {
            $sError = self::BESTITAMAZONPAY_ERROR_CURRENCY_UNSUPPORTED;
        } elseif ($sErrorCode == 'InvalidParameterValue'
            && (stripos($sErrorMessage, 'presentmentCurrency') !== false
                || stripos($sErrorMessage, 'currencyCode') !== false)
        ) {
            $sError = self::BESTITAMAZONPAY_ERROR_CURRENCY_UNSUPPORTED;
        } elseif ($sErrorMessage !== '') {
            // error message directly by amazon pay
            $sError = $sErrorMessage;
        } else {
            $sError = self::BESTITAMAZONPAY_ERROR_AMAZON_TERMINATED;
        }

        /** @var oxUserException $oEx */
        $oEx = $this->_getContainer()->getObjectFactory()->createOxidObject('oxUserException');
        $oEx->setMessage($sError);
        $this->_getContainer()->getUtilsView()->addErrorToDisplay($oEx, false, true);

        //Redirect to user step
        $this->_getContainer()->getUtils()->redirect($oConfig->getShopSecureHomeUrl().'cl=basket', false);
    }

    /**
     * Clears amazon pay variables.
     *
     * @return object
     * @throws Exception
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function render()
    {
        $sClass = $this->_getContainer()->getConfig()->getRequestParameter('cl');

        //If user was let to change payment, don't let him do other shit, just payment selection
        if ($sClass !== 'order'
            && $sClass !== 'thankyou'
            && (bool)$this->_getContainer()->getSession()->getVariable('blAmazonSyncChangePayment') === true
        ) {
            $this->cleanAmazonPay(true);
        }

        return parent::render();
    }

    /**
     * Parent function wrapper.
     *
     * @param null|string $sProductId
     * @param null|float $dAmount
     * @param null|array $aSelectList
     * @param null|array $aPersistentParameters
     * @param bool $blOverride
     *
     * @return mixed
     */
    protected function _parentToBasket(
        $sProductId = null,
        $dAmount = null,
        $aSelectList = null,
        $aPersistentParameters = null,
        $blOverride = false
    ) {
        return parent::tobasket($sProductId, $dAmount, $aSelectList, $aPersistentParameters, $blOverride);
    }

    /**
     * Check if we are using amazon quick checkout.
     *
     * @param null|string $sProductId
     * @param null|float $dAmount
     * @param null|array $aSelectList
     * @param null|array $aPersistentParameters
     * @param bool $blOverride
     *
     * @return mixed
     * @throws oxSystemComponentException
     */
    public function tobasket(
        $sProductId = null,
        $dAmount = null,
        $aSelectList = null,
        $aPersistentParameters = null,
        $blOverride = false
    ) {
        $oContainer = $this->_getContainer();
        $oConfig = $oContainer->getConfig();
        $isAmazonPay = (bool)$oConfig->getRequestParameter('bestitAmazonPayIsAmazonPay');
        $sReturn = null;

        if ($isAmazonPay === true) {
            $oContainer->getBasketUtil()->setQuickCheckoutBasket();
            $sAmazonOrderReferenceId = $oConfig->getRequestParameter('amazonOrderReferenceId');
            $sAccessToken = $oConfig->getRequestParameter('access_token');
            $sReturn = 'user?fnc=amazonLogin&redirectCl=user&amazonOrderReferenceId='.$sAmazonOrderReferenceId
                .'&access_token='.$sAccessToken;
        }

        $sDefaultReturn = $this->_parentToBasket(
            $sProductId,
            $dAmount,
            $aSelectList,
            $aPersistentParameters,
            $blOverride
        );

        if ($isAmazonPay === true) {
            $oSession = $oContainer->getSession();
            $oSession->setVariable('blAddedNewItem', false);
            $oSession->setVariable('isAmazonPayQuickCheckout', true);
            return $sReturn;
        }

        return $sDefaultReturn;
    }
}

