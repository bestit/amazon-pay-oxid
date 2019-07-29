<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_user
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_user
 */
class bestitAmazonPay4OxidUserTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_user
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidUser = new bestitAmazonPay4Oxid_user();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

        self::setValue($oBestitAmazonPay4OxidUser, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidUser;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidUser = new bestitAmazonPay4Oxid_user();
        self::assertInstanceOf('bestitAmazonPay4Oxid_user', $oBestitAmazonPay4OxidUser);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidUser = new bestitAmazonPay4Oxid_user();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidUser, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::render()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(2))
            ->method('getRequestParameter')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls(null, 1));

        $oContainer->expects($this->exactly(2))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oSession = $this->_getSessionMock();

        $oSession->expects($this->once())
            ->method('setVariable')
            ->with('amazonOrderReferenceId', 1);

        $oContainer->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($oSession));

        $oBestitAmazonPay4OxidUser = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidUser->render();
        $oBestitAmazonPay4OxidUser->render();
    }
}
