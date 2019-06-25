<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4OxidBasketUtil
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
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
        $oBestitAmazonPay4OxidBasketUtil->setLogger(new NullLogger());
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
        $oBestitAmazonPay4OxidBasketUtil->setLogger(new NullLogger());
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
        $oBestitAmazonPay4OxidBasketUtil->setLogger(new NullLogger());

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
        $oBestitAmazonPay4OxidBasketUtil->setLogger(new NullLogger());

        $oBestitAmazonPay4OxidBasketUtil->restoreQuickCheckoutBasket();
        $oBestitAmazonPay4OxidBasketUtil->restoreQuickCheckoutBasket();
    }

    /**
     * @group  unit
     * @covers ::getBasketHash()
     * @throws ReflectionException
     * @throws oxArticleException
     * @throws oxArticleInputException
     * @throws oxNoArticleException
     */
    public function testGetBasketHash()
    {
        $oBasket = $this->_getBasketMock();

        $oBasket->expects($this->once())
            ->method('getBruttoSum')
            ->will($this->returnValue(12.34));

        $oBasketItem = $this->getMock('oxBasketItem');

        $oProduct = $this->getMockBuilder('oxArticle')
            ->disableOriginalConstructor()
            ->getMock();;

        $oProduct->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('productId'));

        $oBasketItem->expects($this->once())
            ->method('getArticle')
            ->will($this->returnValue($oProduct));

        $oBasketItem->expects($this->once())
            ->method('getAmount')
            ->will($this->returnValue(321));

        $oBasket->expects($this->once())
            ->method('getContents')
            ->will($this->returnValue(array($oBasketItem)));

        $oBestitAmazonPay4OxidBasketUtil = $this->_getObject(
            $this->_getSessionMock(),
            $this->_getLanguageMock(),
            $this->_getObjectFactoryMock()
        );
        $oBestitAmazonPay4OxidBasketUtil->setLogger(new NullLogger());

        self::assertEquals(
            '382e6274a71c1ab8d5734d158eec4de3',
            $oBestitAmazonPay4OxidBasketUtil->getBasketHash('amazonReferenceId', $oBasket)
        );
    }
}
