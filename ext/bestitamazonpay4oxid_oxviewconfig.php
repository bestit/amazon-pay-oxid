<?php

/**
 * Extension for OXID oxViewConfig class
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_oxViewConfig extends bestitAmazonPay4Oxid_oxViewConfig_parent
{
    /**
     * @var null|bestitAmazonPay4OxidContainer
     */
    protected $_oContainer = null;

    const CODE_INJECTED_STATIC_CACHE_KEY = 'bestitAmazonPay4OxidCodeInjected';

    /**
     * Restore basket if amazon quick checkout was aborted.
     *
     * bestitAmazonPay4Oxid_oxViewConfig constructor.
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function __construct()
    {
        parent::__construct();

        $oContainer = $this->_getContainer();
        $oSession = $oContainer->getSession();

        if ((bool)$oSession->getVariable('isAmazonPayQuickCheckout') === true) {
            $sCurrentClass = $oContainer->getConfig()->getRequestParameter('cl');
            $aCheckoutClasses = array('order', 'payment', 'thankyou', 'user');

            if (in_array($sCurrentClass, $aCheckoutClasses) === false) {
                $oContainer->getBasketUtil()->restoreQuickCheckoutBasket();
                $oSession->deleteVariable('isAmazonPayQuickCheckout');
                $oContainer->getModule()->cleanAmazonPay();
            }
        }
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
     * Method checks if Amazon pay is active and can be used
     *
     * @return boolean true/false
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    public function getAmazonPayIsActive()
    {
        return $this->_getContainer()->getModule()->isActive();
    }

    /**
     * Returns Amazon Model class property by given property name
     *
     * @param string $sPropertyName property name
     *
     * @return mixed
     * @throws oxSystemComponentException
     */
    public function getAmazonProperty($sPropertyName)
    {
        return $this->_getContainer()->getClient()->getAmazonProperty($sPropertyName);
    }

    /**
     * Returns Amazon config value
     *
     * @param string $sConfigVariable Config variable name
     *
     * @return mixed
     * @throws oxSystemComponentException
     */
    public function getAmazonConfigValue($sConfigVariable)
    {
        return $this->_getContainer()->getConfig()->getConfigParam($sConfigVariable);
    }

    /**
     * Method checks if Amazon Login is active and can be used
     *
     * @return boolean true/false
     * @throws oxSystemComponentException
     */
    public function getAmazonLoginIsActive()
    {
        return $this->_getContainer()->getLoginClient()->isActive();
    }

    /**
     * Method checks if Amazon Login is active and can be used
     *
     * @return boolean true/false
     * @throws oxSystemComponentException
     */
    public function showAmazonLoginButton()
    {
        return $this->_getContainer()->getLoginClient()->showAmazonLoginButton();
    }

    /**
     * Method checks if Amazon Pay is active and can be used
     *
     * @return boolean true/false
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    public function showAmazonPayButton()
    {
        return $this->_getContainer()->getLoginClient()->showAmazonPayButton();
    }

    /**
     * Method returns language for Amazon GUI elements
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function getAmazonLanguage()
    {
        return $this->_getContainer()->getLoginClient()->getAmazonLanguage();
    }

    /**
     * Forces to return shop self link if Amazon Login is active and we already have ORO
     * Method is dedicated to stay always in checkout process under SSL
     *
     * @return string
     */
    public function getSelfLink()
    {
        try {
            if ((bool)$this->_getContainer()->getConfig()->getConfigParam('sSSLShopURL') === true
                && !$this->isAdmin()
                && $this->getAmazonLoginIsActive()
            ) {
                return $this->getSslSelfLink();
            }
        } catch (Exception $oException) {
            //Do nothing
        }

        return parent::getSelfLink();
    }

    /**
     * Forces to return basket link if Amazon Login is active and we already have ORO in SSL
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function getBasketLink()
    {
        if ($this->getAmazonLoginIsActive() === true) {
            $sValue = $this->getViewConfigParam('basketlink');

            if ((string)$sValue === '') {
                $sValue = $this->_getContainer()->getConfig()->getShopSecureHomeUrl().'cl=basket';
                $this->setViewConfigParam('basketlink', $sValue);
            }

            return $sValue;
        }

        return parent::getBasketLink();
    }

    /**
     * Loads the injected code map from the static cache.
     *
     * @return array|mixed
     *
     * @throws oxSystemComponentException
     */
    protected function _getInjectedCode()
    {
        $oUtils = $this->_getContainer()->getUtils();
        $aCodeInjected = $oUtils->fromStaticCache(self::CODE_INJECTED_STATIC_CACHE_KEY);
        return $aCodeInjected === null ? array() : $aCodeInjected;
    }

    /**
     * Marks the type as already injected.
     *
     * @param string $sType The type for the js
     *
     * @throws oxSystemComponentException
     */
    public function setJSCodeInjected($sType)
    {
        $aCodeInjected = $this->_getInjectedCode();
        $aCodeInjected[$sType] = true;
        $this->_getContainer()->getUtils()->toStaticCache(self::CODE_INJECTED_STATIC_CACHE_KEY, $aCodeInjected);
    }

    /**
     * Checks if the code with given type was already injected.
     *
     * @param string $sType The type for the js
     *
     * @return bool
     * @throws oxSystemComponentException
     */
    public function wasJSCodeInjected($sType)
    {
        $aCodeInjected = $this->_getInjectedCode();
        return isset($aCodeInjected[$sType]) && $aCodeInjected[$sType];
    }

    /**
     * Returns a unique id.
     *
     * @return string
     */
    public function getUniqueButtonId()
    {
        return uniqid();
    }

    /**
     * Returns the basket currency.
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function getBasketCurrency()
    {
        $oCurrency = $this->_getContainer()->getSession()->getBasket()->getBasketCurrency();

        return $oCurrency !== null ? $oCurrency->name : '';
    }

    /**
     * Returns the session token.
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function getSessionToken()
    {
        return $this->_getContainer()->getSession()->getSessionChallengeToken();
    }

    /**
     * @return string
     *
     * @throws oxSystemComponentException
     */
    public function getBasketHash()
    {
        return $this->_getContainer()->getSession()->getVariable('sAmazonBasketHash');
    }
}
