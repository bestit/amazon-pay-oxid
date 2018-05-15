<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidTest
 * @coversDefaultClass bestitAmazonPay4OxidBasketUtil
 */
class bestitAmazonPay4OxidBasketUtilTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param oxSession                         $oSession
     * @param oxLang                            $oLanguage
     * @param bestitAmazonPay4OxidObjectFactory $oObjectFactory
     *
     * @return bestitAmazonPay4OxidBasketUtil
     * @throws ReflectionException
     */
    private function _getObject(
        oxSession $oSession,
        oxLang $oLanguage,
        bestitAmazonPay4OxidObjectFactory $oObjectFactory
    ) {
        $oBestitAmazonPay4OxidBasketUtil = new bestitAmazonPay4OxidBasketUtil();
        self::setValue($oBestitAmazonPay4OxidBasketUtil, '_oSessionObject', $oSession);
        self::setValue($oBestitAmazonPay4OxidBasketUtil, '_oLanguageObject', $oLanguage);
        self::setValue($oBestitAmazonPay4OxidBasketUtil, '_oObjectFactory', $oObjectFactory);

        return $oBestitAmazonPay4OxidBasketUtil;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidBasketUtil = new bestitAmazonPay4OxidBasketUtil();
        self::assertInstanceOf('bestitAmazonPay4OxidBasketUtil', $oBestitAmazonPay4OxidBasketUtil);
    }

    /**
     * @group  unit
     * @covers ::setQuickCheckoutBasket()
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testSetQuickCheckoutBasket()
    {
        $oOldBasket = $this->_getBasketMock();
        $oNewBasket = $this->_getBasketMock();

        $oSession = $this->_getSessionMock();

        $oSession->expects($this->once())
            ->method('getBasket')
            ->willReturn($oOldBasket);

        $oSession->expects($this->once())
            ->method('setVariable')
            ->with(bestitAmazonPay4OxidBasketUtil::BESTITAMAZONPAY_TEMP_BASKET, serialize($oOldBasket));

        $oSession->expects($this->once())
            ->method('setBasket')
            ->with($oNewBasket);

        $oObjectFactory = $this->_getObjectFactoryMock();

        $oObjectFactory->expects($this->once())
            ->method('createOxidObject')
            ->with('oxBasket')
            ->willReturn($oNewBasket);

        $oBestitAmazonPay4OxidBasketUtil = $this->_getObject(
            $oSession,
            $this->_getLanguageMock(),
            $oObjectFactory
        );

        $oBestitAmazonPay4OxidBasketUtil->setQuickCheckoutBasket();
    }

    /**
     * @group  unit
     * @covers ::restoreQuickCheckoutBasket()
     * @covers ::_validateBasket()
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testRestoreQuickCheckoutBasket()
    {
        $oBasket = $this->_getBasketMock();

        $oBasket->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(array()));

        $oSession = $this->_getSessionMock();

        $oSession->expects($this->exactly(2))
            ->method('getVariable')
            ->with(bestitAmazonPay4OxidBasketUtil::BESTITAMAZONPAY_TEMP_BASKET)
            ->will($this->onConsecutiveCalls(null, serialize($oBasket)));

        $oSession->expects($this->once())
            ->method('setBasket')
            ->with($oBasket);

        $oLanguage = $this->_getLanguageMock();

        $oLanguage->expects($this->once())
            ->method('getBaseLanguage')
            ->will($this->returnValue(1));

        $oObjectFactory = $this->_getObjectFactoryMock();

        $oObjectFactory->expects($this->once())
            ->method('createOxidObject')
            ->with('oxBasketItem');

        $oBestitAmazonPay4OxidBasketUtil = $this->_getObject(
            $oSession,
            $oLanguage,
            $oObjectFactory
        );

        $oBestitAmazonPay4OxidBasketUtil->restoreQuickCheckoutBasket();
        $oBestitAmazonPay4OxidBasketUtil->restoreQuickCheckoutBasket();
    }
}
