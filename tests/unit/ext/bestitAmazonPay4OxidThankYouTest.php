<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_thankyou
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
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
        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_thankyou $oBestitAmazonPay4OxidThankYou */
        $oBestitAmazonPay4OxidThankYou = $this->getMock(
            'bestitAmazonPay4Oxid_thankyou',
            array('_parentInit')
        );

        $oBestitAmazonPay4OxidThankYou->expects($this->any())
            ->method('_parentInit')
            ->will($this->returnValue(null));

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
     * @covers ::init()
     * @covers ::_parentInit()
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testInit()
    {
        $oContainer = $this->_getContainerMock();

        $oBasketUtil = $this->_getBasketUtilMock();

        $oBasketUtil->expects($this->once())
            ->method('restoreQuickCheckoutBasket');

        $oContainer->expects($this->once())
            ->method('getBasketUtil')
            ->will($this->returnValue($oBasketUtil));

        $oBestitAmazonPay4OxidThankYou = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidThankYou->init();
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
