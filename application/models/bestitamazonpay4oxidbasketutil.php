<?php

/**
 * Model for quick checkout handling
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
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
        $this->getLogger()->debug('Store quick checkout basket');

        $oObjectFactory = $this->getObjectFactory();
        $oSession = $this->getSession();

        // Create new temp basket and copy the products to it
        $oCurrentBasket = $oSession->getBasket();
        $oSession->setVariable(self::BESTITAMAZONPAY_TEMP_BASKET, serialize($oCurrentBasket));

        //Reset current basket
        $oSession->setBasket($oObjectFactory->createOxidObject('oxBasket'));
    }

    /**
     * Validates the basket.
     *
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
            $this->getLogger()->debug('Restore quick checkout basket');

            //init oxbasketitem class first #1746
            $this->getObjectFactory()->createOxidObject('oxBasketItem');

            $oBasket = unserialize($sBasket);
            $this->_validateBasket($oBasket);

            //Reset old basket
            $oSession->setBasket($oBasket);
        } else {
            $this->getLogger()->debug('Can\'t restore quick checkout basket');
        }
    }

    /**
     * Generates the basket hash.
     *
     * @param string                                              $sAmazonOrderReferenceId
     * @param oxBasket|\OxidEsales\Eshop\Application\Model\Basket $oBasket
     *
     * @return string
     *
     * @throws oxArticleException
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     */
    public function getBasketHash($sAmazonOrderReferenceId, $oBasket)
    {
        $aBasket = array(
            'amazonOrderReferenceId' => $sAmazonOrderReferenceId,
            'totalSum' => $oBasket->getBruttoSum(),
            'contents' => array()
        );

        /** @var oxBasketItem $oBasketItem */
        foreach ($oBasket->getContents() as $oBasketItem) {
            $sId = $oBasketItem->getArticle()->getId();
            $aBasket['contents'][$sId] = $oBasketItem->getAmount();
        }

        $hash = md5(json_encode($aBasket));
        $this->getLogger()->debug(
            'Generate basket hash',
            array('basket' => $aBasket, 'hash' => $hash)
        );

        return $hash;
    }
}
