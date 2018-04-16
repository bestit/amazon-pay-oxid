<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitamazonpay4oxidaddressutil.php
 *
 * The bestitAmazonPay4OxidAddressUtil class file.
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
 * Class bestitAmazonPay4OxidBasketUtil
 */
class bestitAmazonPay4OxidBasketUtil extends bestitAmazonPay4OxidContainer
{
    const BESTITAMAZONPAY_TEMP_BASKET = 'BESTITAMAZONPAY_TEMP_BASKET';

    /**
     * Stores the basket which is present before the quick checkout.
     *
     * @throws oxSystemComponentException
     */
    public function setQuickCheckoutBasket()
    {
        $oObjectFactory = $this->getObjectFactory();
        $oSession = $this->getSession();

        // Create new temp basket and copy the products to it
        $oCurrentBasket = $oSession->getBasket();
        $oSession->setVariable(self::BESTITAMAZONPAY_TEMP_BASKET, serialize($oCurrentBasket));

        //Reset current basket
        $oSession->setBasket($oObjectFactory->createOxidObject('oxBasket'));
    }

    /**
     * @param oxBasket $oBasket
     */
    protected function _validateBasket($oBasket)
    {
        $aCurrentContent = $oBasket->getContents();
        $iCurrLang = $this->getLanguage()->getBaseLanguage();

        /** @var oxBasketItem $oContent */
        foreach ($aCurrentContent as $oContent) {
            if ($oContent->getLanguageId() !== $iCurrLang) {
                $oContent->setLanguageId($iCurrLang);
            }
        }
    }

    /**
     * Restores the basket which was present before the quick checkout.
     *
     * @throws oxSystemComponentException
     */
    public function restoreQuickCheckoutBasket()
    {
        $oSession = $this->getSession();
        $sBasket = $oSession->getVariable(self::BESTITAMAZONPAY_TEMP_BASKET);

        if ($sBasket !== null) {
            //init oxbasketitem class first #1746
            $this->getObjectFactory()->createOxidObject('oxBasketItem');

            $oBasket = unserialize($sBasket);
            $this->_validateBasket($oBasket);

            //Reset old basket
            $oSession->setBasket($oBasket);
        }
    }
}
