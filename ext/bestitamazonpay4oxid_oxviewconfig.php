<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitAmazonPay4Oxid_oxviewconfig.php
 *
 * The bestitAmazonPay4Oxid_oxViewConfig class file.
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
 * Class bestitAmazonPay4Oxid_oxViewcCnfig
 */
class bestitAmazonPay4Oxid_oxViewConfig extends bestitAmazonPay4Oxid_oxViewConfig_parent
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
}

