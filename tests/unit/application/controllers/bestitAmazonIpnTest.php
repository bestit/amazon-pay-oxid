<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

use \Monolog\Logger;

/**
 * Unit test for class bestitAmazonIpn
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonIpn
 */
class bestitAmazonIpnTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonIpn
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonIpn = new bestitAmazonIpn();
        self::setValue($oBestitAmazonIpn, '_oContainer', $oContainer);

        return $oBestitAmazonIpn;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonIpn = new bestitAmazonIpn();
        self::assertInstanceOf('bestitAmazonIpn', $oBestitAmazonIpn);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonIpn = new bestitAmazonIpn();
        self::assertInstanceOf('bestitAmazonPay4OxidContainer', self::callMethod($oBestitAmazonIpn, '_getContainer'));
    }

    /**
     * @group unit
     * @covers ::render()
     * @covers ::_getContainer()
     * @covers ::_processError()
     * @throws Exception
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(9))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blAmazonERP'),
                array('blAmazonERP'),
                array('sAmazonAuthorize'),
                array('blAmazonERP'),
                array('sAmazonAuthorize'),
                array('blAmazonERP'),
                array('sAmazonAuthorize'),
                array('blAmazonERP'),
                array('sAmazonAuthorize')
            )
            ->will($this->onConsecutiveCalls(
                true,
                false,
                'some',
                false,
                'IPN',
                false,
                'IPN',
                false,
                'IPN'
            ));

        $oContainer->expects($this->exactly(9))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oIpnHandler = $this->_getIpnHandlerMock();
        $oIpnHandler->expects($this->exactly(4))
            ->method('logIPNResponse')
            ->withConsecutive(
                array(Logger::ERROR, 'IPN response handling disabled - ERP mode is ON (Module settings)'),
                array(Logger::ERROR, 'IPN response handling disabled (Module settings)'),
                array(Logger::ERROR, 'SNS message empty or Error while reading SNS message occurred'),
                array(Logger::ERROR, 'Error while handling Amazon response')
            );
        $oIpnHandler->expects($this->exactly(2))
            ->method('processIPNAction')
            ->with('test')
            ->will($this->onConsecutiveCalls(false, true));

        $oContainer->expects($this->exactly(6))
            ->method('getIpnHandler')
            ->will($this->returnValue($oIpnHandler));

        $oBestitAmazonIpn = $this->_getObject($oContainer);
        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array('sError' => 'IPN response handling disabled - ERP mode is ON (Module settings)'),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array('sError' => 'IPN response handling disabled (Module settings)'),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array('sError' => 'SNS message empty or Error while reading SNS message occurred'),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::setValue($oBestitAmazonIpn, '_sInput', dirname(__FILE__).'/../../../fixtures/test');
        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array('sError' => 'Error while handling Amazon response'),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
    }
}
