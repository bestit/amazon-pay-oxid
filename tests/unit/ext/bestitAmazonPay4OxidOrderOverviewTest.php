<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidOrderOverviewTest
 * @coversDefaultClass bestitAmazonPay4Oxid_order_overview
 */
class bestitAmazonPay4OxidOrderOverviewTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_order_overview
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOrderOverview = new bestitAmazonPay4Oxid_order_overview();
        self::setValue($oBestitAmazonPay4OxidOrderOverview, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidOrderOverview;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOrderOverview = new bestitAmazonPay4Oxid_order_overview();
        self::assertInstanceOf('bestitAmazonPay4Oxid_order_overview', $oBestitAmazonPay4OxidOrderOverview);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOrderOverview = new bestitAmazonPay4Oxid_order_overview();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOrderOverview, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::sendorder()
     */
    public function testSendOrder()
    {
        $oContainer = $this->_getContainerMock();

        $oOrder = $this->_getOrderMock();

        $oOrder->expects($this->exactly(2))
            ->method('load')
            ->with(null)
            ->will($this->onConsecutiveCalls(false, true));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxOrder')
            ->will($this->returnValue($oOrder));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        $oClient = $this->_getClientMock();

        $oClient->expects($this->once())
            ->method('capture')
            ->with($oOrder);

        $oContainer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidOrderOverview = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidOrderOverview->sendorder();
        $oBestitAmazonPay4OxidOrderOverview->sendorder();
    }
}
