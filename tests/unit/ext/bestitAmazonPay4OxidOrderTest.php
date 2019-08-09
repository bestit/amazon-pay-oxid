<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_order
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_order
 */
class bestitAmazonPay4OxidOrderTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_order
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $bestitAmazonPay4OxidOrder = new bestitAmazonPay4Oxid_order();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

        self::setValue($bestitAmazonPay4OxidOrder, '_oContainer', $oContainer);

        return $bestitAmazonPay4OxidOrder;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $bestitAmazonPay4OxidOrder = new bestitAmazonPay4Oxid_order();
        self::assertInstanceOf('bestitAmazonPay4Oxid_order', $bestitAmazonPay4OxidOrder);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $bestitAmazonPay4OxidOrder = new bestitAmazonPay4Oxid_order();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($bestitAmazonPay4OxidOrder, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::updateUserWithAmazonData()
     * @covers ::getAmazonBillingAddress()
     * @throws Exception
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function testUpdateUserWithAmazonData()
    {
        $oContainer = $this->_getContainerMock();

        // Client
        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(2))
            ->method('getOrderReferenceDetails')
            //->with($oBasket)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array()
                    )
                )),
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'BillingAddress' => array(
                                'PhysicalAddress' => 'PhysicalAddressValue'
                            )
                        )
                    )
                ))
            ));

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oAddressUtil = $this->_getAddressUtilMock();

        $oAddressUtil->expects($this->once())
            ->method('parseAmazonAddress')
            ->with('PhysicalAddressValue')
            ->will($this->returnValue(array(
                'FirstName' => 'FirstNameValue',
                'LastName' => 'LastNameValue',
                'Street' => 'StreetValue',
                'StreetNr' => 'StreetNrValue',
                'City' => 'CityValue',
                'StateOrRegion' => 'StateOrRegionValue',
                'CountryId' => 'CountryIdValue',
                'PostalCode' => 'PostalCodeValue'
            )));

        $oContainer->expects($this->once())
            ->method('getAddressUtil')
            ->will($this->returnValue($oAddressUtil));

        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_order $bestitAmazonPay4OxidOrder */
        $bestitAmazonPay4OxidOrder = $this->getMock(
            'bestitAmazonPay4Oxid_order',
            array('getUser')
        );
        self::setValue($bestitAmazonPay4OxidOrder, '_oContainer', $oContainer);

        $oUser = $this->_getUserMock();

        $oUser->expects($this->once())
            ->method('assign')
            ->with(array(
                'oxfname' => 'FirstNameValue',
                'oxlname' => 'LastNameValue',
                'oxstreet' => 'StreetValue',
                'oxstreetnr' => 'StreetNrValue',
                'oxcity' => 'CityValue',
                'oxstateid' => 'StateOrRegionValue',
                'oxcountryid' => 'CountryIdValue',
                'oxzip' => 'PostalCodeValue',
                'oxaddinfo' => null,
                'oxcompany' => null,
            ));

        $oUser->expects($this->once())
            ->method('save');

        $bestitAmazonPay4OxidOrder->expects($this->once())
            ->method('getUser')
            ->will($this->returnValue($oUser));

        $bestitAmazonPay4OxidOrder->updateUserWithAmazonData();
        $bestitAmazonPay4OxidOrder->updateUserWithAmazonData();
    }

    /**
     * @group unit
     * @covers ::getCountryName()
     * @throws oxSystemComponentException
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testGetCountryName()
    {
        $oCountry = $this->getMock('oxCountry', array(), array(), '', false);

        $oCountry->expects($this->exactly(2))
            ->method('load')
            ->with('countryId')
            ->will($this->onConsecutiveCalls(false, true));

        $oCountry->expects($this->once())
            ->method('getFieldData')
            ->with('oxTitle')
            ->will($this->returnValue('countryTitle'));

        $oObjectFactory = $this->_getObjectFactoryMock();

        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxCountry')
            ->will($this->returnValue($oCountry));

        $oContainer = $this->_getContainerMock();

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_order $bestitAmazonPay4OxidOrder */
        $bestitAmazonPay4OxidOrder = $this->getMock(
            'bestitAmazonPay4Oxid_order',
            array('getUser')
        );
        self::setValue($bestitAmazonPay4OxidOrder, '_oContainer', $oContainer);

        self::assertEquals('', $bestitAmazonPay4OxidOrder->getCountryName('countryId'));
        self::assertEquals('countryTitle', $bestitAmazonPay4OxidOrder->getCountryName('countryId'));
    }

    /**
     * @group unit
     * @covers ::render()
     * @covers ::_setErrorAndRedirect()
     * @throws Exception
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testRender()
    {
        $oBasket = $this->_getBasketMock();

        $oContainer = $this->_getContainerMock();

        // Config
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(7))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('amazonBasketHash'),
                array('amazonBasketHash'),
                array('fnc'),
                array('action'),
                array('fnc'),
                array('action'),
                array('amazonBasketHash')
            )
            ->will($this->onConsecutiveCalls(
                null,
                'someAmazonBasketHash',
                'someFnc',
                'someAction',
                'someFnc',
                'someAction',
                'someAmazonBasketHash'
            ));

        $oConfig->expects($this->exactly(4))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->exactly(9))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(5))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls(
                null,
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId'
            ));

        $oSession->expects($this->exactly(2))
            ->method('setVariable')
            ->with('sAmazonBasketHash', 'someAmazonBasketHash');

        $oContainer->expects($this->exactly(7))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Client
        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(2))
            ->method('setOrderReferenceDetails')
            //->with($oBasket)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(array(
                    'SetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'Constraints' => array(
                                'Constraint' => array(
                                    'ConstraintID' => 'PaymentMethodNotAllowed'
                                )
                            )
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'SetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderReferenceStatus' => array(
                                'State' => 'NotDraft'
                            )
                        )
                    )
                ))
            ));

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        // ObjectFactory
        $oUserException = $this->getMock('oxUserException');
        $oUserException->expects($this->exactly(3))
            ->method('setMessage')
            ->withConsecutive(
                array('BESTITAMAZONPAY_CHANGE_PAYMENT'),
                array('BESTITAMAZONPAY_NO_PAYMENTS_FOR_SHIPPING_ADDRESS'),
                array('BESTITAMAZONPAY_CHANGE_PAYMENT')
            );

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(3))
            ->method('createOxidObject')
            ->with('oxUserException')
            ->will($this->returnValue($oUserException));

        $oContainer->expects($this->exactly(3))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        // Utils
        $oUtils = $this->_getUtilsMock();
        $oUtils->expects($this->exactly(4))
            ->method('redirect')
            ->withConsecutive(
                array('shopSecureHomeUrl?cl=basket', false),
                array('shopSecureHomeUrl?cl=user', false),
                array('shopSecureHomeUrl?cl=payment', false),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false)
            );

        $oContainer->expects($this->exactly(4))
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        // UtilsView
        $oUtilsView = $this->_getUtilsViewMock();
        $oUtilsView->expects($this->exactly(3))
            ->method('addErrorToDisplay')
            ->with($oUserException, false, true);

        $oContainer->expects($this->exactly(3))
            ->method('getUtilsView')
            ->will($this->returnValue($oUtilsView));

        $bestitAmazonPay4OxidOrder = $this->_getObject($oContainer);

        self::setValue($bestitAmazonPay4OxidOrder, '_blIsOrderStep', false);
        $bestitAmazonPay4OxidOrder->render();

        $oPayment = $this->getMock('oxPayment', array(), array(), '', false);
        $oPayment->expects($this->exactly(5))
            ->method('getId')
            ->will($this->onConsecutiveCalls(
                'bestitamazon',
                'oxempty',
                'bestitamazon',
                'bestitamazon',
                'some'
            ));

        self::setValue($bestitAmazonPay4OxidOrder, '_oPayment', $oPayment);
        self::assertEquals('page/checkout/order.tpl', $bestitAmazonPay4OxidOrder->render());
        self::assertEquals('page/checkout/order.tpl', $bestitAmazonPay4OxidOrder->render());

        self::setValue($bestitAmazonPay4OxidOrder, '_oBasket', $oBasket);
        self::assertEquals('page/checkout/order.tpl', $bestitAmazonPay4OxidOrder->render());
        self::assertEquals('page/checkout/order.tpl', $bestitAmazonPay4OxidOrder->render());
        self::assertEquals('page/checkout/order.tpl', $bestitAmazonPay4OxidOrder->render());
    }

    /**
     * @group unit
     * @covers ::confirmAmazonOrderReference()
     * @throws Exception
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testConfirmAmazonOrderReference()
    {
        $oContainer = $this->_getContainerMock();

        // Config
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(6))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oConfig->expects($this->exactly(6))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('cl'),
                array('cl'),
                array('cl'),
                array('formData'),
                array('cl'),
                array('formData')
            )
            ->will($this->onConsecutiveCalls(
                'some',
                'order',
                'order',
                'formDataOne=1&amp;formDataTwo=2',
                'order',
                'formDataOne=1&amp;formDataTwo=2'
            ));

        $oContainer->expects($this->exactly(6))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(6))
            ->method('checkSessionChallenge')
            ->will($this->onConsecutiveCalls(false, true, true, true, true, true));

        $oBasket = $this->_getBasketMock();
        $oBasket->expects($this->exactly(5))
            ->method('getPaymentId')
            ->will($this->onConsecutiveCalls(
                'some',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon'
            ));

        $oSession->expects($this->exactly(5))
            ->method('getBasket')
            ->will($this->returnValue($oBasket));

        $oSession->expects($this->exactly(3))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls(
                null,
                'amazonOrderReferenceId',
                'amazonOrderReferenceId'
            ));

        $oContainer->expects($this->exactly(6))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // BasketUtil
        $oBasketUtil = $this->_getBasketUtilMock();

        $oBasketUtil->expects($this->exactly(2))
            ->method('getBasketHash')
            ->with('amazonOrderReferenceId', $oBasket)
            ->will($this->returnValue('basketHash'));

        $oContainer->expects($this->exactly(2))
            ->method('getBasketUtil')
            ->will($this->returnValue($oBasketUtil));

        // Client
        $oClient = $this->_getClientMock();

        $oClient->expects($this->exactly(2))
            ->method('confirmOrderReference')
            ->with(array(
                'success_url' => 'shopSecureHomeUrl?formDataOne=1&formDataTwo=2&amazonBasketHash=basketHash',
                'failure_url' => 'shopSecureHomeUrl?cl=user&fnc=processAmazonCallback&cancelOrderReference=1'
            ))
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(array('Error' => 'someError')),
                $this->_getResponseObject(array())
            ));

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_order $bestitAmazonPay4OxidOrder */
        $bestitAmazonPay4OxidOrder = $this->getMock('bestitAmazonPay4Oxid_order', array('renderJson'));
        self::setValue($bestitAmazonPay4OxidOrder, '_oContainer', $oContainer);

        $sFailureJson = '{"success":false,"redirectUrl":"shopSecureHomeUrl?cl=user&fnc=processAmazonCallback&cancelOrderReference=1"}';

        $bestitAmazonPay4OxidOrder->expects($this->exactly(6))
            ->method('renderJson')
            ->withConsecutive(
                array($sFailureJson),
                array($sFailureJson),
                array($sFailureJson),
                array($sFailureJson),
                array($sFailureJson),
                array('{"success":true,"redirectUrl":"shopSecureHomeUrl?cl=user&fnc=processAmazonCallback&cancelOrderReference=1"}')
            );

        // Invalid session challenge
        $bestitAmazonPay4OxidOrder->confirmAmazonOrderReference();

        // No amazon order
        $bestitAmazonPay4OxidOrder->confirmAmazonOrderReference();
        $bestitAmazonPay4OxidOrder->confirmAmazonOrderReference();

        // Missing Amazon order reference id
        $bestitAmazonPay4OxidOrder->confirmAmazonOrderReference();

        // Error on client call
        $bestitAmazonPay4OxidOrder->confirmAmazonOrderReference();

        // Success
        $bestitAmazonPay4OxidOrder->confirmAmazonOrderReference();
    }
}
