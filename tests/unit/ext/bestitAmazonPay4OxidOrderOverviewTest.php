<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_order_overview
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_order_overview
 */
class bestitAmazonPay4OxidOrderOverviewTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_order_overview
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOrderOverview = new bestitAmazonPay4Oxid_order_overview();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

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
     * @throws ReflectionException
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
     * @throws Exception
     */
    public function testSendOrder()
    {
        $oContainer = $this->_getContainerMock();

        $oOrder = $this->_getOrderMock();

        $oOrder->expects($this->exactly(3))
            ->method('load')
            ->with(null)
            ->will($this->onConsecutiveCalls(false, true, true));

        $oOrder->expects($this->exactly(3))
            ->method('getFieldData')
            ->withConsecutive(array('oxPaymentType'),array('oxPaymentType'), array('oxordernr'))
            ->will($this->onConsecutiveCalls('some', 'bestitamazon', 'number'));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(3))
            ->method('createOxidObject')
            ->with('oxOrder')
            ->will($this->returnValue($oOrder));

        $oContainer->expects($this->exactly(3))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        $oClient = $this->_getClientMock();

        $oClient->expects($this->once())
            ->method('saveCapture')
            ->with($oOrder);

        $oContainer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidOrderOverview = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidOrderOverview->sendorder();
        $oBestitAmazonPay4OxidOrderOverview->sendorder();
        $oBestitAmazonPay4OxidOrderOverview->sendorder();
    }
}
