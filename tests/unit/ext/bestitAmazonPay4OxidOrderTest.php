<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxDeliverySetListTest
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
                'oxcity' => 'CityValue',
                'oxstateid' => 'StateOrRegionValue',
                'oxcountryid' => 'CountryIdValue',
                'oxzip' => 'PostalCodeValue'
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
        $oConfig->expects($this->exactly(6))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('fnc'),
                array('action'),
                array('fnc'),
                array('action'),
                array('fnc'),
                array('action')
            )
            ->will($this->onConsecutiveCalls(
                array('someFnc'),
                array('someAction'),
                array('someFnc'),
                array('someAction'),
                array('someFnc'),
                array('someAction')
            ));

        $oConfig->expects($this->exactly(4))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->exactly(6))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));
        
        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(6))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls(
                null,
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId'
            ));

        $oContainer->expects($this->exactly(6))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Client
        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(3))
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
                )),
                $this->_getResponseObject(array(
                    'SetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderReferenceStatus' => array(
                                'State' => 'Draft'
                            )
                        )
                    )
                ))
            ));

        $oContainer->expects($this->exactly(3))
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
        $oPayment->expects($this->exactly(6))
            ->method('getId')
            ->will($this->onConsecutiveCalls(
                'bestitamazon',
                'oxempty',
                'bestitamazon',
                'bestitamazon',
                'some',
                'bestitamazon'
            ));

        self::setValue($bestitAmazonPay4OxidOrder, '_oPayment', $oPayment);
        $bestitAmazonPay4OxidOrder->render();
        $bestitAmazonPay4OxidOrder->render();

        self::setValue($bestitAmazonPay4OxidOrder, '_oBasket', $oBasket);
        $bestitAmazonPay4OxidOrder->render();
        $bestitAmazonPay4OxidOrder->render();
        $bestitAmazonPay4OxidOrder->render();
        $bestitAmazonPay4OxidOrder->render();
    }
}
