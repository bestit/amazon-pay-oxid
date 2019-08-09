<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_oxcmp_basket
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_oxcmp_basket
 */
class bestitAmazonPay4OxidOxCmpBasketTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxcmp_basket
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOxCmpBasket = new bestitAmazonPay4Oxid_oxcmp_basket();
        self::setValue($oBestitAmazonPay4OxidOxCmpBasket, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidOxCmpBasket;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOxCmpBasket = new bestitAmazonPay4Oxid_oxcmp_basket();
        self::assertInstanceOf('bestitAmazonPay4Oxid_oxcmp_basket', $oBestitAmazonPay4OxidOxCmpBasket);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOxCmpBasket = new bestitAmazonPay4Oxid_oxcmp_basket();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOxCmpBasket, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::processAmazonCallback()
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function testProcessAmazonCallback()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(2))
            ->method('getRequestParameter')
            ->with('AuthenticationStatus')
            ->will($this->onConsecutiveCalls(
                'Abandoned',
                'some'
            ));

        $oConfig->expects($this->once())
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->exactly(2))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->once())
            ->method('setVariable')
            ->with('blAmazonSyncChangePayment', 1);

        $oContainer->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Utils
        $oUtils = $this->_getUtilsMock();
        $oUtils->expects($this->once())
            ->method('redirect')
            ->with('shopSecureHomeUrl?cl=order&action=changePayment', false);

        $oContainer->expects($this->once())
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        $oBestitAmazonPay4OxidOxCmpBasket = $this->getMock(
            'bestitAmazonPay4Oxid_oxcmp_basket',
            array('cleanAmazonPay')
        );

        self::setValue($oBestitAmazonPay4OxidOxCmpBasket, '_oContainer', $oContainer);

        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_oxcmp_basket $oBestitAmazonPay4OxidOxCmpBasket */
        $oBestitAmazonPay4OxidOxCmpBasket->expects($this->once())
            ->method('cleanAmazonPay');

        $oBestitAmazonPay4OxidOxCmpBasket->processAmazonCallback();
        $oBestitAmazonPay4OxidOxCmpBasket->processAmazonCallback();
    }

    /**
     * @group unit
     * @covers ::render()
     * @covers ::cleanAmazonPay()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws Exception
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(11))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('cl'),
                array('cl'),
                array('cl'),
                array('cl'),
                array('cancelOrderReference'),
                array('bestitAmazonPay4OxidErrorCode'),
                array('error'),
                array('cl'),
                array('cancelOrderReference'),
                array('bestitAmazonPay4OxidErrorCode'),
                array('error')
            )
            ->will($this->onConsecutiveCalls(
                'order',
                'thankyou',
                'some',
                'some',
                true,
                '',
                '',
                'some',
                false,
                '',
                'errorValue'
            ));
        $oConfig->expects($this->exactly(2))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->exactly(7))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(5))
            ->method('getVariable')
            ->withConsecutive(
                array('blAmazonSyncChangePayment'),
                array('blAmazonSyncChangePayment'),
                array('amazonOrderReferenceId'),
                array('blAmazonSyncChangePayment'),
                array('amazonOrderReferenceId')
            )
            ->will($this->onConsecutiveCalls(
                false,
                true,
                'amazonOrderReferenceIdValue',
                true,
                'amazonOrderReferenceIdValue'
            ));

        $oContainer->expects($this->exactly(5))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Module
        $oModule = $this->_getModuleMock();
        $oModule->expects($this->exactly(2))
            ->method('cleanAmazonPay');

        $oContainer->expects($this->exactly(2))
            ->method('getModule')
            ->will($this->returnValue($oModule));

        // ObjectFactory
        $oUserException = $this->getMock('oxUserException');
        $oUserException->expects($this->exactly(2))
            ->method('setMessage')
            ->withConsecutive(
                array(bestitAmazonPay4Oxid_oxcmp_basket::BESTITAMAZONPAY_ERROR_AMAZON_TERMINATED),
                array('errorValue')
            );

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxUserException')
            ->will($this->returnValue($oUserException));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        // UtilsView
        $oUtilsView = $this->_getUtilsViewMock();
        $oUtilsView->expects($this->exactly(2))
            ->method('addErrorToDisplay')
            ->withConsecutive(
                array($oUserException, false, true)
            );

        $oContainer->expects($this->exactly(2))
            ->method('getUtilsView')
            ->will($this->returnValue($oUtilsView));

        // Utils
        $oUtils = $this->_getUtilsMock();
        $oUtils->expects($this->exactly(2))
            ->method('redirect')
            ->withConsecutive(
                array('shopSecureHomeUrl?cl=basket', false),
                array('shopSecureHomeUrl?cl=basket', false)
            );

        $oContainer->expects($this->exactly(2))
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        // Client
        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(2))
            ->method('cancelOrderReference')
            ->with(null, array('amazon_order_reference_id' => 'amazonOrderReferenceIdValue'));

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidOxCmpBasket = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
        $oBestitAmazonPay4OxidOxCmpBasket->render();
    }

    /**
     * @group unit
     * @covers ::tobasket()
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testToBasket()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(5))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('bestitAmazonPayIsAmazonPay'),
                array('bestitAmazonPayIsAmazonPay'),
                array('bestitAmazonPayIsAmazonPay'),
                array('amazonOrderReferenceId'),
                array('access_token')
            )
            ->will($this->onConsecutiveCalls(
                null,
                null,
                1,
                'amazonOrderReferenceId',
                'accessToken'
            ));

        $oContainer->expects($this->exactly(3))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(2))
            ->method('setVariable')
            ->withConsecutive(
                array('blAddedNewItem', false),
                array('isAmazonPayQuickCheckout', true)
            );

        $oContainer->expects($this->once())
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // BasketUtil
        $oBasketUtil = $this->_getBasketUtilMock();
        $oBasketUtil->expects($this->once())
            ->method('setQuickCheckoutBasket');

        $oContainer->expects($this->once())
            ->method('getBasketUtil')
            ->will($this->returnValue($oBasketUtil));

        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_oxcmp_basket $oBestitAmazonPay4OxidOxCmpBasket */
        $oBestitAmazonPay4OxidOxCmpBasket = $this->getMock(
            'bestitAmazonPay4Oxid_oxcmp_basket',
            array('_parentToBasket')
        );

        self::setValue($oBestitAmazonPay4OxidOxCmpBasket, '_oContainer', $oContainer);

        $oBestitAmazonPay4OxidOxCmpBasket->expects($this->exactly(3))
            ->method('_parentToBasket')
            ->withConsecutive(
                array(null, null, null, null, false),
                array(null, null, null, null, false),
                array('productId', 1.0, array('selectList'), array('persistenParameter'), true)
            )
            ->will($this->returnValue('parentReturn'));

        self::assertEquals('parentReturn', $oBestitAmazonPay4OxidOxCmpBasket->tobasket());
        self::assertEquals('parentReturn', $oBestitAmazonPay4OxidOxCmpBasket->tobasket());
        self::assertEquals(
            'user?fnc=amazonLogin&redirectCl=user&amazonOrderReferenceId=amazonOrderReferenceId&access_token=accessToken',
            $oBestitAmazonPay4OxidOxCmpBasket->tobasket(
                'productId',
                1.0,
                array('selectList'),
                array('persistenParameter'),
                true
            )
        );
    }
}
