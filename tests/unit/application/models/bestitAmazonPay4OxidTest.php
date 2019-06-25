<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';


/**
 * Unit test for class bestitAmazonPay4Oxid
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid
 */
class bestitAmazonPay4OxidTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param oxUser|bool       $oUser
     * @param oxConfig          $oConfig
     * @param DatabaseInterface $oDatabase
     * @param oxSession         $oSession
     *
     * @return bestitAmazonPay4Oxid
     * @throws ReflectionException
     */
    private function _getObject(
        $oUser,
        oxConfig $oConfig,
        DatabaseInterface $oDatabase,
        oxSession $oSession, bestitAmazonPay4OxidObjectFactory $oObjectFactory
    ) {
        $oBestitAmazonPay4Oxid = new bestitAmazonPay4Oxid();
        $oBestitAmazonPay4Oxid->setLogger(new NullLogger());
        self::setValue($oBestitAmazonPay4Oxid, '_oActiveUserObject', $oUser);
        self::setValue($oBestitAmazonPay4Oxid, '_oConfigObject', $oConfig);
        self::setValue($oBestitAmazonPay4Oxid, '_oDatabaseObject', $oDatabase);
        self::setValue($oBestitAmazonPay4Oxid, '_oSessionObject', $oSession);
        self::setValue($oBestitAmazonPay4Oxid, '_oObjectFactory', $oObjectFactory);

        return $oBestitAmazonPay4Oxid;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4Oxid = new bestitAmazonPay4Oxid();
        $oBestitAmazonPay4Oxid->setLogger(new NullLogger());
        self::assertInstanceOf('bestitAmazonPay4Oxid', $oBestitAmazonPay4Oxid);
    }

    /**
     * @group  unit
     * @covers ::getIsSelectedCurrencyAvailable
     * @throws ReflectionException
     */
    public function testGetIsSelectedCurrencyAvailable()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(16))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonLocale'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency')
            )
            ->will($this->onConsecutiveCalls(
                false,
                'DE',
                false,
                false,
                'DE',
                false,
                'UK',
                false,
                'UK',
                false,
                'US',
                false,
                'US',
                false,
                'ST',
                true
            ));

        $aCurrentReturns = array('ST', 'EUR', 'ST', 'GBP', 'ST', 'USD', 'ST');

        $oBasket = $this->_getBasketMock();
        $oBasket->expects($this->exactly(7))
            ->method('getBasketCurrency')
            ->will($this->returnCallback(function () use (&$aCurrentReturns) {
                $oCurrency = new stdClass();
                $oCurrency->name = array_shift($aCurrentReturns);
                return $oCurrency;
            }));

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(7))
            ->method('getBasket')
            ->will($this->returnValue($oBasket));

        $oBestitAmazonPay4Oxid = $this->_getObject(
            $this->_getUserMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $oSession,
            $this->_getObjectFactoryMock()
        );

        $oBestitAmazonPay4Oxid->setLogger(new NullLogger());

        self::assertFalse($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::assertFalse($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertTrue($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertFalse($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertTrue($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertFalse($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertTrue($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertTrue($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', null);
        self::assertTrue($oBestitAmazonPay4Oxid->getIsSelectedCurrencyAvailable());
    }

    /**
     * @group  unit
     * @covers ::isActive()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testIsActive()
    {
        $this->setConfigParam('blSkipViewUsage', true);
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(9))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonSellerId'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonSellerId'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonSellerId'),
                array('blBestitAmazonPay4OxidEnableMultiCurrency'),
                array('sAmazonSellerId')
            )
            ->will($this->onConsecutiveCalls(
                false,
                false,
                null,
                false,
                'sellerId',
                false,
                'sellerId',
                false,
                'sellerId',
                false
            ));


        $oConfig->expects($this->exactly(3))
            ->method('getRequestParameter')
            ->with('cl')
            ->will($this->onConsecutiveCalls(
               'details',
               'some',
               'some'
            ));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(6))
            ->method('quote')
            ->with('shippingId')
            ->will($this->returnCallback(function ($sValue) {
                return "'{$sValue}'";
            }));

        $sActiveQuery = "SELECT OXACTIVE
            FROM oxpayments
            WHERE OXID = 'bestitamazon'";

        $sShippingQuery = "SELECT OXOBJECTID
            FROM oxobject2payment AS o2p RIGHT JOIN oxdeliveryset AS d 
              ON (o2p.OXOBJECTID = d.OXID AND d.OXACTIVE = 1)
            WHERE OXPAYMENTID = 'bestitamazon'
              AND OXTYPE='oxdelset'
            LIMIT 1";

        $sDeliverySetQuery = "SELECT OXID
            FROM oxdel2delset
            WHERE OXDELSETID = 'shippingId'
            LIMIT 1";

        $oDatabase->expects($this->exactly(21))
            ->method('getOne')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sDeliverySetQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sDeliverySetQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sDeliverySetQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sDeliverySetQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sDeliverySetQuery)),
                array(new MatchIgnoreWhitespace($sActiveQuery)),
                array(new MatchIgnoreWhitespace($sShippingQuery)),
                array(new MatchIgnoreWhitespace($sDeliverySetQuery))
            )->will($this->onConsecutiveCalls(
                0,
                1,
                false,
                1,
                'shippingId',
                false,
                1,
                'shippingId',
                'deliverySetId',
                1,
                'shippingId',
                'deliverySetId',
                1,
                'shippingId',
                'deliverySetId',
                1,
                'shippingId',
                'deliverySetId',
                1,
                'shippingId',
                'deliverySetId'
            ));

        $oBasket = $this->_getBasketMock();
        $oPrice = $this->_getPriceMock();
        $oPrice->expects($this->exactly(2))
            ->method('getBruttoPrice')
            ->will($this->onConsecutiveCalls(0, 0.3));

        $oBasket->expects($this->exactly(2))
            ->method('getPrice')
            ->will($this->returnValue($oPrice));

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(2))
            ->method('getBasket')
            ->will($this->returnValue($oBasket));

        $oBestitAmazonPay4Oxid = $this->_getObject(
            $this->_getUserMock(),
            $oConfig,
            $oDatabase,
            $oSession,
            $this->_getObjectFactoryMock()
        );

        $oBestitAmazonPay4Oxid->setLogger(new NullLogger());

        self::assertFalse($oBestitAmazonPay4Oxid->isActive());
        self::assertAttributeEquals(false, '_blActive', $oBestitAmazonPay4Oxid);
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', false);
        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_isSelectedCurrencyAvailable', true);
        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertTrue($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertTrue($oBestitAmazonPay4Oxid->isActive());
    }

    /**
     * @group  unit
     * @covers ::cleanAmazonPay()
     * @covers ::cleanUpUnusedAccounts()
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testCleanAmazonPay()
    {
        $oUser = $this->_getUserMock();
        $oUser->expects($this->exactly(2))
            ->method('getFieldData')
            ->with('oxusername')
            ->will($this->onConsecutiveCalls('some', '1@amazon.com'));


        $oUser->expects($this->once())
            ->method('delete');

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(3))
            ->method('getAll')
            ->with(new MatchIgnoreWhitespace(
                'SELECT oxid, oxusername
                FROM oxuser
                WHERE oxusername LIKE \'%-%-%@amazon.com\'
                AND oxcreate < (NOW() - INTERVAL 1440 MINUTE)'
            ))
            ->will($this->onConsecutiveCalls(
                array(),
                array(),
                array(array('oxid' => 1), array('oxid' => 2)))
            );

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(3))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->returnValue('1'));

        $oSession->expects($this->exactly(15))
            ->method('deleteVariable')
            ->withConsecutive(
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('blAmazonSyncChangePayment'),
                array('sAmazonBasketHash'),
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('blAmazonSyncChangePayment'),
                array('sAmazonBasketHash'),
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('blAmazonSyncChangePayment'),
                array('sAmazonBasketHash')
            );

        $oCreatedUser = $this->_getUserMock();
        $oCreatedUser->expects($this->exactly(2))
            ->method('load')
            ->will($this->onConsecutiveCalls(false, true));

        $oCreatedUser->expects($this->once())
            ->method('delete');

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxUser')
            ->will($this->returnValue($oCreatedUser));

        $oBestitAmazonPay4Oxid = $this->_getObject(
            false,
            $this->_getConfigMock(),
            $oDatabase,
            $oSession,
            $oObjectFactory
        );

        $oBestitAmazonPay4Oxid->setLogger(new NullLogger());

        $oBestitAmazonPay4Oxid->cleanAmazonPay();
        self::setValue($oBestitAmazonPay4Oxid, '_oActiveUserObject', $oUser);
        $oBestitAmazonPay4Oxid->cleanAmazonPay();
        $oBestitAmazonPay4Oxid->cleanAmazonPay();
    }
}
