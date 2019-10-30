<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';


/**
 * Unit test for class bestitAmazonPay4Oxid_oxOrder
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_oxOrder
 */
class bestitAmazonPay4OxidOxOrderTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @return string
     */
    private function _getTestInstanceName()
    {
        return (class_exists('bestitAmazonPay4Oxid_oxOrder_oxid5') === true) ?
            'bestitAmazonPay4Oxid_oxOrder_oxid5' : 'bestitAmazonPay4Oxid_oxOrder';
    }

    /**
     * @return bestitAmazonPay4Oxid_oxOrder|bestitAmazonPay4Oxid_oxOrder_oxid5
     */
    private function _getTestInstance()
    {
        if (class_exists('bestitAmazonPay4Oxid_oxOrder_oxid5') === true) {
            return new bestitAmazonPay4Oxid_oxOrder_oxid5();
        }

        return new bestitAmazonPay4Oxid_oxOrder();
    }

    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxOrder
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOxOrder = $this->_getTestInstance();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

        self::setValue($oBestitAmazonPay4OxidOxOrder, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidOxOrder;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOxOrder = $this->_getTestInstance();
        self::assertInstanceOf($this->_getTestInstanceName(), $oBestitAmazonPay4OxidOxOrder);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOxOrder = $this->_getTestInstance();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOxOrder, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::_preFinalizeOrder()
     * @covers ::_callSyncAmazonAuthorize()
     * @covers ::_manageFullUserData()
     * @covers ::_performAmazonActions()
     * @throws ReflectionException
     */
    public function testPreFinalizeOrder()
    {
        $oContainer = $this->_getContainerMock();

        // Config
        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(19))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('cl'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash'),
                array('cl'),
                array('amazonBasketHash')
            )
            ->will($this->onConsecutiveCalls(
                'order',
                'order',
                null,
                'order',
                null,
                'order',
                'basketHash',
                'order',
                'basketHash',
                'order',
                'basketHash',
                'order',
                'basketHash',
                'order',
                'basketHash',
                'order',
                'basketHash',
                'order',
                'basketHash',
                'order',
                'basketHash'
            ));

        $oConfig->expects($this->exactly(7))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oConfig->expects($this->exactly(3))
            ->method('getShopId')
            ->will($this->returnValue(456));

        $oConfig->expects($this->exactly(14))
            ->method('getConfigParam')
            ->withConsecutive(
                array('sAmazonMode'),
                array('blAmazonERP'),
                array('sAmazonMode'),
                array('blAmazonERP'),
                array('sAmazonMode'),
                array('blAmazonERP'),
                array('sAmazonMode'),
                array('blAmazonERP'),
                array('sAmazonMode'),
                array('blAmazonERP'),
                array('sAmazonMode'),
                array('blAmazonERP'),
                array('sAmazonMode'),
                array('blAmazonERP')
            )
            ->will($this->onConsecutiveCalls(
                bestitAmazonPay4OxidClient::BASIC_FLOW,
                0,
                bestitAmazonPay4OxidClient::BASIC_FLOW,
                0,
                bestitAmazonPay4OxidClient::BASIC_FLOW,
                0,
                bestitAmazonPay4OxidClient::BASIC_FLOW,
                0,
                bestitAmazonPay4OxidClient::BASIC_FLOW,
                0,
                bestitAmazonPay4OxidClient::BASIC_FLOW,
                0,
                bestitAmazonPay4OxidClient::OPTIMIZED_FLOW,
                0
            ));

        $oContainer->expects($this->exactly(21))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();

        $oSession->expects($this->exactly(15))
            ->method('getVariable')
            ->withConsecutive(
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('sAmazonBasketHash'),
                array('amazonOrderReferenceId'),
                array('sAmazonBasketHash'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('deladrid'),
                array('amazonOrderReferenceId'),
                array('deladrid')
            )
            ->will($this->onConsecutiveCalls(
                null,
                'orderReferenceId',
                null,
                'orderReferenceId',
                'basketHash',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'deliveryId',
                'orderReferenceId',
                null
            ));

        $oSession->expects($this->exactly(7))
            ->method('setVariable')
            ->withConsecutive(
                array('blAmazonSyncChangePayment', 1),
                array('sAmazonSyncResponseState', 'Open'),
                array('sAmazonSyncResponseAuthorizationId', 'authorizationId'),
                array('blshowshipaddress', 1),
                array('deladrid', 'newAddressId'),
                array('sAmazonSyncResponseState', 'Open'),
                array('sAmazonSyncResponseAuthorizationId', 'authorizationId')
            );

        $oContainer->expects($this->exactly(21))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Client
        $oClient = $this->_getClientMock();

        $aOrderReferenceOpen = array(
            'GetOrderReferenceDetailsResult' => array(
                'OrderReferenceDetails' => array(
                    'OrderReferenceStatus' => array('State' => 'Open'),
                    'Destination' => array('PhysicalDestination' => 'PhysicalDestinationValue'),
                    'Buyer' => array('Email' => 'BuyerEmail')
                )
            )
        );

        $oClient->expects($this->exactly(8))
            ->method('getOrderReferenceDetails')
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderReferenceStatus' => array(
                                'State' => 'NotOpen'
                            )
                        )
                    )
                )),
                $this->_getResponseObject($aOrderReferenceOpen),
                $this->_getResponseObject($aOrderReferenceOpen),
                $this->_getResponseObject($aOrderReferenceOpen),
                $this->_getResponseObject($aOrderReferenceOpen),
                $this->_getResponseObject($aOrderReferenceOpen),
                $this->_getResponseObject($aOrderReferenceOpen),
                $this->_getResponseObject($aOrderReferenceOpen)
            ));

        $oClient->expects($this->exactly(7))
            ->method('authorize')
            ->with(
                null,
                array(
                    'amazon_order_reference_id' => 'orderReferenceId',
                    'authorization_amount' => 11.1,
                    'currency_code' => 'EUR',
                    'authorization_reference_id' => 'orderReferenceId_123456789'
                )
            )
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'AuthorizeResult' => array(
                        'AuthorizationDetails' => array(
                            'AuthorizationStatus' => array(
                                'State' => 'Closed',
                                'ReasonCode' => 'InvalidPaymentMethod'
                            )
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'AuthorizeResult' => array(
                        'AuthorizationDetails' => array(
                            'AuthorizationStatus' => array(
                                'State' => 'Declined',
                                'ReasonCode' => 'Some'
                            )
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'AuthorizeResult' => array(
                        'AuthorizationDetails' => array(
                            'AuthorizationStatus' => array(
                                'State' => 'Some'
                            )
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'AuthorizeResult' => array(
                        'AuthorizationDetails' => array(
                            'AmazonAuthorizationId' => 'authorizationId',
                            'AuthorizationStatus' => array(
                                'State' => 'Open'
                            )
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'AuthorizeResult' => array(
                        'AuthorizationDetails' => array(
                            'AmazonAuthorizationId' => 'authorizationId',
                            'AuthorizationStatus' => array(
                                'State' => 'Open'
                            )
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'AuthorizeResult' => array(
                        'AuthorizationDetails' => array(
                            'AmazonAuthorizationId' => 'authorizationId',
                            'AuthorizationStatus' => array(
                                'State' => 'Declined',
                                'ReasonCode' => 'TransactionTimedOut'
                            )
                        )
                    )
                ))
            ));

        $oContainer->expects($this->exactly(16))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        // Utils
        $oUtils = $this->_getUtilsMock();

        $oUtils->expects($this->exactly(7))
            ->method('redirect')
            ->withConsecutive(
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=order&action=changePayment', false),
                array(
                    'shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay&error=BESTITAMAZONPAY_PAYMENT_DECLINED_OR_REJECTED',
                    false
                ),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false)
            );

        $oContainer->expects($this->exactly(18))
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        // UtilsDate
        $oUtilsDate = $this->_getUtilsDateMock();

        $oUtilsDate->expects($this->exactly(7))
            ->method('getTime')
            ->will($this->returnValue('123456789'));

        $oContainer->expects($this->exactly(7))
            ->method('getUtilsDate')
            ->will($this->returnValue($oUtilsDate));

        // AddressUtil
        $oAddressUtil = $this->_getAddressUtilMock();

        $oAddressUtil->expects($this->exactly(3))
            ->method('parseAmazonAddress')
            ->with('PhysicalDestinationValue')
            ->will($this->returnValue(array(
                'CompanyName' => 'CompanyNameValue',
                'FirstName' => 'FirstNameValue',
                'LastName' => 'LastNameValue',
                'City' => 'CityValue',
                'StateOrRegion' => 'StateOrRegionValue',
                'CountryId' => 'CountryIdValue',
                'PostalCode' => 'PostalCodeValue',
                'Phone' => 'PhoneValue',
                'Street' => 'StreetValue',
                'StreetNr' => 'StreetNrValue',
                'AddInfo' => 'AddInfoValue'
            )));

        $oContainer->expects($this->exactly(3))
            ->method('getAddressUtil')
            ->will($this->returnValue($oAddressUtil));

        // ObjectFactory
        $oObjectFactory = $this->_getObjectFactoryMock();

        $oAddress = $this->getMock('oxAddress', array(), array(), '', false);
        $oAddress->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                array('firstAddressId'),
                array('deliveryId')
            );

        $oAddress->expects($this->exactly(2))
            ->method('assign')
            ->withConsecutive(
                array(
                    array(
                        'oxcompany' => 'CompanyNameValue',
                        'oxfname' => 'FirstNameValue',
                        'oxlname' => 'LastNameValue',
                        'oxcity' => 'CityValue',
                        'oxstateid' => 'StateOrRegionValue',
                        'oxcountryid' => 'CountryIdValue',
                        'oxzip' => 'PostalCodeValue',
                        'oxfon' => 'PhoneValue',
                        'oxstreet' => 'StreetValue',
                        'oxstreetnr' => 'StreetNrValue',
                        'oxaddinfo' => 'AddInfoValue',
                        'oxuserid' => 'emailId'
                    )
                ),
                array(
                    array(
                        'oxcompany' => 'CompanyNameValue',
                        'oxfname' => 'FirstNameValue',
                        'oxlname' => 'LastNameValue',
                        'oxcity' => 'CityValue',
                        'oxstateid' => 'StateOrRegionValue',
                        'oxcountryid' => 'CountryIdValue',
                        'oxzip' => 'PostalCodeValue',
                        'oxfon' => 'PhoneValue',
                        'oxstreet' => 'StreetValue',
                        'oxstreetnr' => 'StreetNrValue',
                        'oxaddinfo' => 'AddInfoValue'
                    )
                )
            );

        $oAddress->expects($this->exactly(2))
            ->method('save')
            ->will($this->returnValue('newAddressId'));

        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxAddress')
            ->will($this->returnValue($oAddress));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        // Database
        $oDatabase = $this->_getDatabaseMock();

        $oDatabase->expects($this->exactly(3))
            ->method('getOne')
            ->with(new MatchIgnoreWhitespace(
                "SELECT OXID
                FROM oxuser
                WHERE OXUSERNAME = 'BuyerEmail'
                AND OXSHOPID = '456'"
            ))
            ->will($this->onConsecutiveCalls('emailId', ''));

        $oDatabase->expects($this->exactly(6))
            ->method('quote')
            ->withConsecutive(
                array('BuyerEmail'),
                array(456),
                array('BuyerEmail'),
                array(456),
                array('BuyerEmail'),
                array(456)
            )
            ->will($this->returnCallback(function ($sValue) {
                return "'{$sValue}'";
            }));

        $oContainer->expects($this->exactly(3))
            ->method('getDatabase')
            ->will($this->returnValue($oDatabase));

        $oBestitAmazonPay4OxidOxOrder = $this->_getObject($oContainer);

        $oBasket = $this->_getBasketMock();

        $oBasket->expects($this->exactly(11))
            ->method('getPaymentId')
            ->will($this->onConsecutiveCalls(
                'some',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon',
                'bestitamazon'
            ));

        $oBasket->expects($this->any())
            ->method('getContents')
            ->will($this->returnValue(array()));

        $oPrice = $this->_getPriceMock();
        $oPrice->expects($this->exactly(7))
            ->method('getBruttoPrice')
            ->will($this->returnValue(11.1));

        $oBasket->expects($this->exactly(7))
            ->method('getPrice')
            ->will($this->returnValue($oPrice));

        $oCurrency = new stdClass();
        $oCurrency->name = 'EUR';

        $oBasket->expects($this->exactly(7))
            ->method('getBasketCurrency')
            ->will($this->returnValue($oCurrency));

        $oUser = $this->_getUserMock();

        $oFirstAddress = $this->getMock('oxAddress', array(), array(), '', false);
        $oFirstAddress->expects($this->exactly(4))
            ->method('getFieldData')
            ->withConsecutive(
                array('oxfname'),
                array('oxlname'),
                array('oxstreet'),
                array('oxstreetnr')
            )
            ->will($this->onConsecutiveCalls(
                'FirstNameValue',
                'LastNameValue',
                'StreetValue',
                'StreetNrValue'
            ));

        $oFirstAddress->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('firstAddressId'));

        $oUser->expects($this->once())
            ->method('getUserAddresses')
            ->will($this->returnValue(array($oFirstAddress)));

        $oBasketUtil = $this->_getBasketUtilMock();

        $oBasketUtil->expects($this->exactly(9))
            ->method('getBasketHash')
            ->with('orderReferenceId', $oBasket)
            ->will($this->returnValue('basketHash'));

        $oContainer->expects($this->exactly(9))
            ->method('getBasketUtil')
            ->will($this->returnValue($oBasketUtil));

        $blIsAmazonOrder = null;
        $blAuthorizeAsync = null;

        // No amazon order
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertTrue($blReturn);
        self::assertFalse($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Empty amazonOrderReferenceId
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Empty confirmOrderReference
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Status not open
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Error authorize
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Error InvalidPaymentMethod
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Cancel ORO
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Unexpected behaviour
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertFalse($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Logged in user already and Amazon address set as shipping
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertTrue($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // Logged in user but we have found user account in OXID with same email
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertTrue($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertFalse($blAuthorizeAsync);

        // All right
        $blReturn = self::callMethod(
            $oBestitAmazonPay4OxidOxOrder,
            '_preFinalizeOrder',
            array(&$oBasket, &$oUser, &$blIsAmazonOrder, &$blAuthorizeAsync)
        );
        self::assertTrue($blReturn);
        self::assertTrue($blIsAmazonOrder);
        self::assertTrue($blAuthorizeAsync);
    }

    /**
     * @group unit
     * @covers ::_performAmazonActions()
     * @throws ReflectionException
     */
    public function testPerformAmazonActions()
    {
        $oContainer = $this->_getContainerMock();

        // Session
        $oSession = $this->_getSessionMock();

        $oSession->expects($this->exactly(6))
            ->method('getVariable')
            ->withConsecutive(
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('sAmazonSyncResponseState'),
                array('sAmazonSyncResponseAuthorizationId'),
                array('sAmazonSyncResponseState'),
                array('amazonOrderReferenceId')
            )
            ->will($this->onConsecutiveCalls(
                'orderReferenceId',
                'orderReferenceId',
                'amazonSyncResponseState',
                'amazonSyncResponseAuthorizationId',
                'Open',
                'orderReferenceId'
            ));

        $oContainer->expects($this->exactly(3))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Config
        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(5))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blAmazonERP'),
                array('sAmazonERPModeStatus'),
                array('blAmazonERP'),
                array('sAmazonCapture'),
                array('blAmazonERP')
            )
            ->will($this->onConsecutiveCalls(
                1,
                'ERPModeStatus',
                0,
                'DIRECT',
                0
            ));

        $oContainer->expects($this->exactly(3))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oBestitAmazonPay4OxidOxOrder = $this->_getObject($oContainer);

        // Client
        $oClient = $this->_getClientMock();

        $oClient->expects($this->once())
            ->method('capture');

        $oClient->expects($this->once())
            ->method('authorize');

        $oClient->expects($this->exactly(3))
            ->method('setOrderAttributes')
            ->with($oBestitAmazonPay4OxidOxOrder);

        $oContainer->expects($this->exactly(5))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        self::callMethod($oBestitAmazonPay4OxidOxOrder, '_performAmazonActions', array(false));
        self::callMethod($oBestitAmazonPay4OxidOxOrder, '_performAmazonActions', array(false));
        self::callMethod($oBestitAmazonPay4OxidOxOrder, '_performAmazonActions', array(true));
    }

    /**
     * @group unit
     * @covers ::finalizeOrder()
     * @covers ::_parentFinalizeOrder()
     * @throws Exception
     */
    public function testFinalizeOrder()
    {
        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_oxOrder $oBestitAmazonPay4OxidOxOrder */
        $oBestitAmazonPay4OxidOxOrder = $this->getMock(
            $this->_getTestInstanceName(),
            array('_preFinalizeOrder', '_performAmazonActions', '_parentFinalizeOrder')
        );

        $oContainer = $this->_getContainerMock();

        // Client
        $oClient = $this->_getClientMock();
        $oClient->expects($this->once())
            ->method('cancelOrderReference');

        $oContainer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($oClient));

        self::setValue($oBestitAmazonPay4OxidOxOrder, '_oContainer', $oContainer);

        $preReturn = false;

        $oBestitAmazonPay4OxidOxOrder->expects($this->exactly(3))
            ->method('_preFinalizeOrder')
            ->will($this->returnCallback(function ($oBasket, $oUser, &$blIsAmazonOrder) use (&$preReturn) {
                $blIsAmazonOrder = true;

                if ($preReturn === false) {
                    $preReturn = true;
                    return false;
                }

                return $preReturn;
            }));

        $oBestitAmazonPay4OxidOxOrder->expects($this->exactly(2))
            ->method('_parentFinalizeOrder')
            ->will($this->onConsecutiveCalls(
                oxOrder::ORDER_STATE_INVALIDDELIVERY,
                oxOrder::ORDER_STATE_OK
            ));

        $oBestitAmazonPay4OxidOxOrder->expects($this->once())
            ->method('_performAmazonActions');


        $oBasket = $this->_getBasketMock();
        $oBasket->method('getContents')
            ->willReturn(array());

        $oBasket->expects($this->any())
            ->method('getPaymentId')
            ->will($this->onConsecutiveCalls(
                'some'
            ));

        $oUser = $this->_getUserMock();
        self::assertEquals(
            oxOrder::ORDER_STATE_PAYMENTERROR,
            $oBestitAmazonPay4OxidOxOrder->finalizeOrder($oBasket, $oUser)
        );
        self::assertEquals(
            oxOrder::ORDER_STATE_INVALIDDELIVERY,
            $oBestitAmazonPay4OxidOxOrder->finalizeOrder($oBasket, $oUser)
        );
        self::assertEquals(
            oxOrder::ORDER_STATE_OK,
            $oBestitAmazonPay4OxidOxOrder->finalizeOrder($oBasket, $oUser)
        );

        self::assertEquals(
            oxOrder::ORDER_STATE_INVALIDDELIVERY,
            self::callMethod(
                $this->_getObject($this->_getContainerMock()),
                '_parentFinalizeOrder',
                array($oBasket, $oUser, false)
            )
        );
    }

    /**
     * @group unit
     * @covers ::validateDeliveryAddress()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testValidateDeliveryAddress()
    {
        $oContainer = $this->_getContainerMock();

        $oBasket = $this->_getBasketMock();

        $oBasket->expects($this->exactly(2))
            ->method('getPaymentId')
            ->will($this->onConsecutiveCalls(
                'bestitamazon',
                'some'
            ));

        // Session
        $oSession = $this->_getSessionMock();

        $oSession->expects($this->exactly(2))
            ->method('getBasket')
            ->will($this->returnValue($oBasket));

        $oContainer->expects($this->exactly(2))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        $oBestitAmazonPay4OxidOxOrder = $this->_getObject($oContainer);

        $oUser = $this->_getUserMock();
        self::assertEquals(0, $oBestitAmazonPay4OxidOxOrder->validateDeliveryAddress($oUser));
        self::assertEquals(7, $oBestitAmazonPay4OxidOxOrder->validateDeliveryAddress($oUser));
    }

    /**
     * @group unit
     * @covers ::getAmazonChangePaymentLink()
     * @throws Exception
     */
    public function testGetAmazonChangePaymentLink()
    {
        $oContainer = $this->_getContainerMock();

        // Client
        $oClient = $this->_getClientMock();

        $oClient->expects($this->exactly(2))
            ->method('getAmazonProperty')
            ->with('sAmazonPayChangeLink', true)
            ->will($this->returnValue('paymentChangeLink'));

        $oClient->expects($this->exactly(2))
            ->method('getOrderReferenceDetails')
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderLanguage' => 'de-DE'
                        )
                    )
                ))
            ));

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidOxOrder = $this->_getObject($oContainer);

        self::assertEquals('paymentChangeLink', $oBestitAmazonPay4OxidOxOrder->getAmazonChangePaymentLink());
        self::assertEquals('paymentChangeLinkde_DE', $oBestitAmazonPay4OxidOxOrder->getAmazonChangePaymentLink());
    }
}
