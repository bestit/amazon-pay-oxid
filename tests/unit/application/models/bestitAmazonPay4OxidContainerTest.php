<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4OxidContainer
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4OxidContainer
 */
class bestitAmazonPay4OxidContainerTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidContainer', $oBestitAmazonPay4OxidContainer);
    }

    /**
     * @group unit
     * @covers ::getActiveUser()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testGetActiveUser()
    {
        $oUser = $this->_getUserMock();
        $oUser->expects($this->exactly(2))
            ->method('loadActiveUser')
            ->will($this->onConsecutiveCalls(false, true));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->will($this->returnValue($oUser));

        $oContainer = new bestitAmazonPay4OxidContainer();
        self::setValue($oContainer, '_oObjectFactory', $oObjectFactory);

        self::assertFalse($oContainer->getActiveUser());
        self::assertFalse($oContainer->getActiveUser());

        self::setValue($oContainer, '_oActiveUserObject', null);
        self::assertInstanceOf('oxUser', $oContainer->getActiveUser());
    }

    /**
     * @group unit
     * @covers ::getAddressUtil()
     */
    public function testGetAddressUtil()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidAddressUtil', $oContainer->getAddressUtil());
    }

    /**
     * @group unit
     * @covers ::getClient()
     */
    public function testGetClient()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidClient', $oContainer->getClient());
    }

    /**
     * @group unit
     * @covers ::getConfig()
     */
    public function testGetConfig()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxConfig', $oContainer->getConfig());
    }

    /**
     * @group unit
     * @covers ::getDatabase()
     * @throws oxConnectionException
     */
    public function testGetDatabase()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('DatabaseInterface', $oContainer->getDatabase());
    }

    /**
     * @group unit
     * @covers ::getIpnHandler()
     */
    public function testGetIpnHandler()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidIpnHandler', $oContainer->getIpnHandler());
    }

    /**
     * @group unit
     * @covers ::getLanguage()
     */
    public function testGetLanguage()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxLang', $oContainer->getLanguage());
    }

    /**
     * @group unit
     * @covers ::getLoginClient()
     */
    public function testGetLoginClient()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidLoginClient', $oContainer->getLoginClient());
    }

    /**
     * @group unit
     * @covers ::getModule()
     */
    public function testGetModule()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4Oxid', $oContainer->getModule());
    }

    /**
     * @group unit
     * @covers ::getObjectFactory()
     */
    public function testGetObjectFactory()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidObjectFactory', $oContainer->getObjectFactory());
    }

    /**
     * @group unit
     * @covers ::getSession()
     */
    public function testGetSession()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxSession', $oContainer->getSession());
    }

    /**
     * @group unit
     * @covers ::getUtilsDate()
     */
    public function testGetUtilsDate()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxUtilsDate', $oContainer->getUtilsDate());
    }

    /**
     * @group unit
     * @covers ::getUtilsServer()
     */
    public function testGetUtilsServer()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxUtilsServer', $oContainer->getUtilsServer());
    }

    /**
     * @group unit
     * @covers ::getUtils()
     */
    public function testGetUtils()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxUtils', $oContainer->getUtils());
    }

    /**
     * @group unit
     * @covers ::getUtilsView()
     */
    public function testGetUtilsView()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('oxUtilsView', $oContainer->getUtilsView());
    }

    /**
     * @group unit
     * @covers ::getBasketUtil()
     */
    public function testGetBasketUtil()
    {
        $oContainer = new bestitAmazonPay4OxidContainer();
        self::assertInstanceOf('bestitAmazonPay4OxidBasketUtil', $oContainer->getBasketUtil());
    }
}
