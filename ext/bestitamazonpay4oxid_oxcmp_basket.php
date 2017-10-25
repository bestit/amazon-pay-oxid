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
    /**
     * @var null|bestitAmazonPay4OxidContainer
     */
    protected $_oContainer = null;

    /**
     * Returns the active user object.
     *
     * @return bestitAmazonPay4OxidContainer
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
     */
    public function cleanAmazonPay()
    {
        //Clean all related variables with user data and amazon reference id
        $this->_getContainer()->getModule()->cleanAmazonPay();
        $oConfig = $this->_getContainer()->getConfig();
        $sError = (string)$oConfig->getRequestParameter('error');

        //Bind redirect message if previous was not set
        if ($sError === '') {
            $sError = 'BESTITAMAZONPAY_ERROR_AMAZON_TERMINATED';
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
     * @return mixed
     */
    public function render()
    {
        $sClass = $this->_getContainer()->getConfig()->getRequestParameter('cl');

        //If user was let to change payment, don't let him do other shit, just payment selection
        if ($sClass !== 'order'
            && $sClass !== 'thankyou'
            && (bool)$this->_getContainer()->getSession()->getVariable('blAmazonSyncChangePayment') === true
        ) {
            $this->cleanAmazonPay();
        }

        return parent::render();
    }
}

