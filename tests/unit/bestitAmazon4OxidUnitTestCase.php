<?php

use AmazonPay\Client;
use AmazonPay\ResponseParser;
use AmazonPay\IpnHandler;
use Monolog\Logger;
use Psr\Log\NullLogger;

/**
 * Abstract class oxUnitTestCase to provide some functions needed on module unit tests
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
abstract class bestitAmazon4OxidUnitTestCase extends oxUnitTestCase
{
    /**
     * Calls a private or protected object method.
     *
     * @param object $object
     * @param string $methodName
     * @param array  $arguments
     *
     * @return mixed
     * @throws ReflectionException
     */
    public static function callMethod($object, $methodName, array $arguments = array())
    {
        $class = new \ReflectionClass($object);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $arguments);
    }

    /**
     * Sets a private property
     *
     * @param object $object
     * @param string $valueName
     * @param mixed  $value
     * @throws ReflectionException
     */
    public static function setValue($object, $valueName, $value)
    {
        $reflection = new \ReflectionClass($object);
        $property = $reflection->getProperty($valueName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }

    /**
     * @param array $aResponse
     *
     * @return stdClass
     */
    protected function _getResponseObject(array $aResponse = array())
    {
        return (object) json_decode(json_encode($aResponse));
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject| bestitAmazonPay4Oxid
     */
    protected function _getModuleMock()
    {
        return $this->getMock('bestitAmazonPay4Oxid');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidAddressUtil
     */
    protected function _getAddressUtilMock()
    {
        return $this->getMock('bestitAmazonPay4OxidAddressUtil', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Client
     */
    protected function _getAmazonClientMock()
    {
        return $this->getMock('\AmazonPay\Client', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ResponseParser
     */
    protected function _getAmazonResponseParserMock()
    {
        return $this->getMock('\AmazonPay\ResponseParser');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxBasket
     */
    protected function _getBasketMock()
    {
        return $this->getMock('oxBasket');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidBasketUtil
     */
    protected function _getBasketUtilMock()
    {
        return $this->getMock('bestitAmazonPay4OxidBasketUtil');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxConfig
     */
    protected function _getConfigMock()
    {
        return $this->getMock('oxConfig');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidClient
     */
    protected function _getClientMock()
    {
        return $this->getMock('bestitAmazonPay4OxidClient');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidLoginClient
     */
    protected function _getLoginClientMock()
    {
        return $this->getMock('bestitAmazonPay4OxidLoginClient');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidContainer
     */
    protected function _getContainerMock()
    {
        $container = $this->getMock('bestitAmazonPay4OxidContainer');
        $container
            ->method('getLogger')
            ->willReturn(new NullLogger());

        return $container;
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|DatabaseInterface
     */
    protected function _getDatabaseMock()
    {
        return $this->getMock('DatabaseInterface');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidIpnHandler
     */
    protected function _getIpnHandlerMock()
    {
        return $this->getMock('bestitAmazonPay4OxidIpnHandler');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxLang
     */
    protected function _getLanguageMock()
    {
        return $this->getMock('oxLang');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxUser
     */
    protected function _getUserMock()
    {
        return $this->getMock('oxUser', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxSession
     */
    protected function _getSessionMock()
    {
        return $this->getMock('oxSession');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxShop
     */
    protected function _getShopMock()
    {
        return $this->getMock('oxShop', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxOrder
     */
    protected function _getOrderMock()
    {
        return $this->getMock('oxOrder', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxUtils
     */
    protected function _getUtilsMock()
    {
        return $this->getMock('oxUtils');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxUtilsDate
     */
    protected function _getUtilsDateMock()
    {
        return $this->getMock('oxUtilsDate');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxUtilsServer
     */
    protected function _getUtilsServerMock()
    {
        return $this->getMock('oxUtilsServer');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxUtilsView
     */
    protected function _getUtilsViewMock()
    {
        return $this->getMock('oxUtilsView');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxPrice
     */
    protected function _getPriceMock()
    {
        return $this->getMock('oxPrice');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4OxidObjectFactory
     */
    protected function _getObjectFactoryMock()
    {
        return $this->getMock('bestitAmazonPay4OxidObjectFactory');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_oxEmail
     */
    protected function _getExtendedEmailMock()
    {
        return $this->getMock('bestitAmazonPay4Oxid_oxEmail', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|Logger
     */
    protected function _getLoggerMock()
    {
        return $this->getMock('\Monolog\Logger', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|IpnHandler
     */
    protected function _getAmazonIpnHandlerMock()
    {
        return $this->getMock('\AmazonPay\IpnHandler', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxModule
     */
    protected function _getOxidModuleMock()
    {
        return $this->getMock('oxModule');
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxModuleCache
     */
    protected function _getOxidModuleCacheMock()
    {
        return $this->getMock('oxModuleCache', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxModuleInstaller
     */
    protected function _getOxidModuleInstallerMock()
    {
        return $this->getMock('oxModuleInstaller', array(), array(), '', false);
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|oxDbMetaDataHandler
     */
    protected function _getOxidDbMetaDataHandler()
    {
        return $this->getMock('oxDbMetaDataHandler');
    }
}
