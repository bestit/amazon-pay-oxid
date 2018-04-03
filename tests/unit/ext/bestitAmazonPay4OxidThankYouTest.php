<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidOrderMainTest
 * @coversDefaultClass bestitAmazonPay4Oxid_thankyou
 */
class bestitAmazonPay4OxidThankYouTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_thankyou
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidThankYou = new bestitAmazonPay4Oxid_thankyou();
        self::setValue($oBestitAmazonPay4OxidThankYou, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidThankYou;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidThankYou = new bestitAmazonPay4Oxid_thankyou();
        self::assertInstanceOf('bestitAmazonPay4Oxid_thankyou', $oBestitAmazonPay4OxidThankYou);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidThankYou = new bestitAmazonPay4Oxid_thankyou();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidThankYou, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::render()
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oModule = $this->_getModuleMock();

        $oModule->expects($this->once())
            ->method('cleanAmazonPay');

        $oContainer->expects($this->once())
            ->method('getModule')
            ->will($this->returnValue($oModule));

        $oBestitAmazonPay4OxidThankYou = $this->_getObject($oContainer);

        $oBasket = $this->getMock('oxBasket');
        $oBasket->expects($this->once())
            ->method('getProductsCount')
            ->will($this->returnValue(10));

        self::setValue($oBestitAmazonPay4OxidThankYou, '_oBasket', $oBasket);
        $oBestitAmazonPay4OxidThankYou->render();
    }
}
