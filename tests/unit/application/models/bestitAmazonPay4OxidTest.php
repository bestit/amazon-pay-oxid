<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

use PHPUnit_Extensions_Constraint_StringMatchIgnoreWhitespace as MatchIgnoreWhitespace;

/**
 * Class bestitAmazonPay4OxidTest
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
     */
    private function _getObject(
        $oUser,
        oxConfig $oConfig,
        DatabaseInterface $oDatabase,
        oxSession $oSession, bestitAmazonPay4OxidObjectFactory $oObjectFactory
    ) {
        $oBestitAmazonPay4Oxid = new bestitAmazonPay4Oxid();
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
        self::assertInstanceOf('bestitAmazonPay4Oxid', $oBestitAmazonPay4Oxid);
    }

    /**
     * @group  unit
     * @covers ::getIsSelectedCurrencyAvailable
     */
    public function testGetIsSelectedCurrencyAvailable()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(7))
            ->method('getConfigParam')
            ->will($this->onConsecutiveCalls('DE', 'DE', 'UK', 'UK', 'US', 'US', 'ST'));

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
    }

    /**
     * @group  unit
     * @covers ::isActive()
     */
    public function testIsActive()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(3))
            ->method('getConfigParam')
            ->with('sAmazonSellerId')
            ->will($this->onConsecutiveCalls(null, 'sellerId', 'sellerId'));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(5))
            ->method('quote')
            ->with('shippingId')
            ->will($this->returnCallback(function ($sValue) {
                return "'{$sValue}'";
            }));

        $sActiveQuery = "SELECT OXACTIVE
            FROM oxv_oxpayments_de
            WHERE OXID = 'bestitamazon'";

        $sShippingQuery = "SELECT OXOBJECTID
            FROM oxobject2payment AS o2p RIGHT JOIN oxv_oxdeliveryset_1_de AS d 
              ON (o2p.OXOBJECTID = d.OXID AND d.OXACTIVE = 1)
            WHERE OXPAYMENTID = 'bestitamazon'
              AND OXTYPE='oxdelset'
            LIMIT 1";

        $sDeliverySetQuery = "SELECT OXID
            FROM oxdel2delset
            WHERE OXDELSETID = 'shippingId'
            LIMIT 1";

        $oDatabase->expects($this->exactly(18))
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
                'deliverySetId'
            ));

        $oBasket = $this->_getBasketMock();
        $oPrice = $this->_getPriceMock();
        $oPrice->expects($this->exactly(2))
            ->method('getBruttoPrice')
            ->will($this->onConsecutiveCalls(0, 1));

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
        self::assertFalse($oBestitAmazonPay4Oxid->isActive());

        self::setValue($oBestitAmazonPay4Oxid, '_blActive', null);
        self::assertTrue($oBestitAmazonPay4Oxid->isActive());
    }

    /**
     * @group  unit
     * @covers ::cleanAmazonPay()
     * @covers ::cleanUpUnusedAccounts()
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

        $oSession->expects($this->exactly(12))
            ->method('deleteVariable')
            ->withConsecutive(
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('blAmazonSyncChangePayment'),
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('blAmazonSyncChangePayment'),
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('blAmazonSyncChangePayment')
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

        $oBestitAmazonPay4Oxid->cleanAmazonPay();
        self::setValue($oBestitAmazonPay4Oxid, '_oActiveUserObject', $oUser);
        $oBestitAmazonPay4Oxid->cleanAmazonPay();
        $oBestitAmazonPay4Oxid->cleanAmazonPay();
    }
}
