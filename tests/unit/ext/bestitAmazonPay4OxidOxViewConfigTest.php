<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidOxViewConfigTest
 * @coversDefaultClass bestitAmazonPay4Oxid_oxViewConfig
 */
class bestitAmazonPay4OxidOxViewConfigTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxViewConfig
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOxViewConfig = new bestitAmazonPay4Oxid_oxViewConfig();
        self::setValue($oBestitAmazonPay4OxidOxViewConfig, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidOxViewConfig;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOxViewConfig = new bestitAmazonPay4Oxid_oxViewConfig();
        self::assertInstanceOf('bestitAmazonPay4Oxid_oxViewConfig', $oBestitAmazonPay4OxidOxViewConfig);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOxViewConfig = new bestitAmazonPay4Oxid_oxViewConfig();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOxViewConfig, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::getAmazonPayIsActive()
     */
    public function testGetAmazonPayIsActive()
    {
        $oContainer = $this->_getContainerMock();

        $oModule = $this->_getModuleMock();
        $oModule->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue(true));

        $oContainer->expects($this->once())
            ->method('getModule')
            ->will($this->returnValue($oModule));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertTrue($oBestitAmazonPay4OxidOxViewConfig->getAmazonPayIsActive());
    }

    /**
     * @group unit
     * @covers ::getAmazonProperty()
     */
    public function testGetAmazonProperty()
    {
        $oContainer = $this->_getContainerMock();

        $oClient = $this->_getClientMock();
        $oClient->expects($this->once())
            ->method('getAmazonProperty')
            ->with('property')
            ->will($this->returnValue('propertyReturn'));

        $oContainer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertEquals('propertyReturn', $oBestitAmazonPay4OxidOxViewConfig->getAmazonProperty('property'));
    }

    /**
     * @group unit
     * @covers ::getAmazonConfigValue()
     */
    public function testGetAmazonConfigValue()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('getConfigParam')
            ->with('configVariable')
            ->will($this->returnValue('configVariableReturn'));

        $oContainer->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertEquals(
            'configVariableReturn',
            $oBestitAmazonPay4OxidOxViewConfig->getAmazonConfigValue('configVariable')
        );
    }

    /**
     * @group unit
     * @covers ::getAmazonLoginIsActive()
     */
    public function testGetAmazonLoginIsActive()
    {
        $oContainer = $this->_getContainerMock();

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue(true));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertTrue($oBestitAmazonPay4OxidOxViewConfig->getAmazonLoginIsActive());
    }

    /**
     * @group unit
     * @covers ::showAmazonLoginButton()
     */
    public function testShowAmazonLoginButton()
    {
        $oContainer = $this->_getContainerMock();

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('showAmazonLoginButton')
            ->will($this->returnValue(true));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertTrue($oBestitAmazonPay4OxidOxViewConfig->showAmazonLoginButton());
    }

    /**
     * @group unit
     * @covers ::showAmazonPayButton()
     */
    public function testShowAmazonPayButton()
    {
        $oContainer = $this->_getContainerMock();

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('showAmazonPayButton')
            ->will($this->returnValue(true));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertTrue($oBestitAmazonPay4OxidOxViewConfig->showAmazonPayButton());
    }

    /**
     * @group unit
     * @covers ::getAmazonLanguage()
     */
    public function testGetAmazonLanguage()
    {
        $oContainer = $this->_getContainerMock();

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('getAmazonLanguage')
            ->will($this->returnValue('language'));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertEquals('language', $oBestitAmazonPay4OxidOxViewConfig->getAmazonLanguage());
    }

    /**
     * @group unit
     * @covers ::getSelfLink()
     */
    public function testGetSelfLink()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(2))
            ->method('getConfigParam')
            ->with('sSSLShopURL')
            ->will($this->onConsecutiveCalls(false, true));

        $oContainer->expects($this->exactly(2))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue(true));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertRegExp('/.*index\.php\?$/', $oBestitAmazonPay4OxidOxViewConfig->getSelfLink());
        self::assertRegExp('/.*index\.php\?$/', $oBestitAmazonPay4OxidOxViewConfig->getSelfLink());
    }

    /**
     * @group unit
     * @covers ::getBasketLink()
     */
    public function testGetBasketLink()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->exactly(3))
            ->method('isActive')
            ->will($this->onConsecutiveCalls(false, true, true));

        $oContainer->expects($this->exactly(3))
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxViewConfig = $this->_getObject($oContainer);
        self::assertRegExp('/.*index\.php\?cl=basket$/', $oBestitAmazonPay4OxidOxViewConfig->getBasketLink());

        self::setValue($oBestitAmazonPay4OxidOxViewConfig, '_aViewData', array('basketlink' => 'basketlinkValue'));
        self::assertEquals('basketlinkValue', $oBestitAmazonPay4OxidOxViewConfig->getBasketLink());

        self::setValue($oBestitAmazonPay4OxidOxViewConfig, '_aViewData', array('basketlink' => ''));
        self::assertEquals('shopSecureHomeUrl?cl=basket', $oBestitAmazonPay4OxidOxViewConfig->getBasketLink());
    }
}
