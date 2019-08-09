<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';


/**
 * Unit test for class bestitAmazonPay4Oxid_oxDeliverySetList
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_oxDeliverySetList
 */
class bestitAmazonPay4OxidOxDeliverySetListTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxDeliverySetList
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxDeliverySetList = new bestitAmazonPay4Oxid_oxDeliverySetList();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

        self::setValue($oBestitAmazonPay4OxDeliverySetList, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxDeliverySetList;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxDeliverySetList = new bestitAmazonPay4Oxid_oxDeliverySetList();
        self::assertInstanceOf('bestitAmazonPay4Oxid_oxDeliverySetList', $oBestitAmazonPay4OxDeliverySetList);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxDeliverySetList = new bestitAmazonPay4Oxid_oxDeliverySetList();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxDeliverySetList, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::getDeliverySetData()
     * @covers ::_processResult()
     * @covers ::_getShippingAvailableForPayment()
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function testGetDeliverySetData()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(4))
            ->method('getRequestParameter')
            ->with('cl')
            ->will($this->onConsecutiveCalls(
                'payment',
                'payment',
                'some',
                'some'
            ));

        $oConfig->expects($this->once())
            ->method('getConfigParam')
            ->will($this->returnValue(1));

        $oContainer->expects($this->exactly(4))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(4))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls(
                null,
                'referenceId',
                null,
                'referenceId'
            ));

        $oContainer->expects($this->exactly(4))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        $oModule = $this->_getModuleMock();
        $oModule->expects($this->exactly(2))
            ->method('isActive')
            ->will($this->onConsecutiveCalls(
                false,
                true
            ));

        $oModule->expects($this->once())
            ->method('getIsSelectedCurrencyAvailable')
            ->will($this->returnValue(true));

        $oContainer->expects($this->exactly(3))
            ->method('getModule')
            ->will($this->returnValue($oModule));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(4))
            ->method('quote')
            ->withConsecutive(
                array('shippingOne'),
                array('bestitamazon'),
                array('shippingTwo'),
                array('bestitamazon')
            )
            ->will($this->returnCallback(function ($sValue) {
                return "'{$sValue}'";
            }));


        $oDatabase->expects($this->exactly(2))
            ->method('getOne')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace(
                    "SELECT OXOBJECTID
                    FROM oxobject2payment
                    WHERE OXOBJECTID = 'shippingOne'
                      AND OXPAYMENTID = 'bestitamazon'
                      AND OXTYPE = 'oxdelset'
                      AND OXTYPE = 'oxdelset' LIMIT 1"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXOBJECTID
                    FROM oxobject2payment
                    WHERE OXOBJECTID = 'shippingTwo'
                      AND OXPAYMENTID = 'bestitamazon'
                      AND OXTYPE = 'oxdelset'
                      AND OXTYPE = 'oxdelset' LIMIT 1"
                ))
            )
            ->will($this->onConsecutiveCalls('someId', null));

        $oContainer->expects($this->exactly(2))
            ->method('getDatabase')
            ->will($this->returnValue($oDatabase));

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('showAmazonPayButton')
            ->will($this->returnValue(false));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxDeliverySetList = $this->_getObject($oContainer);

        $aParentReturn = array(
            array(
                'shippingOne' => 'someValue',
                'shippingTwo' => 'someOtherValue'
            ),
            'shipSet',
            array(
                'bestitamazon' => 'someValue',
                'someOther' => 'someOtherValue'
            )
        );

        $oUser = $this->_getUserMock();

        $oPrice = $this->_getPriceMock();
        $oPrice->expects($this->once())
            ->method('getBruttoPrice')
            ->will($this->returnValue(10));

        $oBasket = $this->_getBasketMock();
        $oBasket->expects($this->once())
            ->method('getPrice')
            ->will($this->returnValue($oPrice));

        $aReturn = self::callMethod(
            $oBestitAmazonPay4OxDeliverySetList,
            '_processResult',
            array($aParentReturn, $oUser, $oBasket)
        );
        self::assertEquals(array('someOther'), array_keys($aReturn[2]));

        $aReturn = self::callMethod(
            $oBestitAmazonPay4OxDeliverySetList,
            '_processResult',
            array($aParentReturn, $oUser, $oBasket)
        );
        self::assertEquals(array('shippingOne'), array_keys($aReturn[0]));
        self::assertEquals(array('bestitamazon'), array_keys($aReturn[2]));

        $aReturn = self::callMethod(
            $oBestitAmazonPay4OxDeliverySetList,
            '_processResult',
            array($aParentReturn, $oUser, $oBasket)
        );
        self::assertEquals(array('someOther'), array_keys($aReturn[2]));

        $oBestitAmazonPay4OxDeliverySetList->getDeliverySetData('shippingSet', $oUser, $oBasket);
    }
}
