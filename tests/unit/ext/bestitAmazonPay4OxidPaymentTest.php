<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';


/**
 * Unit test for class bestitAmazonPay4Oxid_payment
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_payment
 */
class bestitAmazonPay4OxidPaymentTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_payment
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $bestitAmazonPay4OxidPayment = new bestitAmazonPay4Oxid_payment();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

        self::setValue($bestitAmazonPay4OxidPayment, '_oContainer', $oContainer);

        return $bestitAmazonPay4OxidPayment;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $bestitAmazonPay4OxidPayment = new bestitAmazonPay4Oxid_payment();
        self::assertInstanceOf('bestitAmazonPay4Oxid_payment', $bestitAmazonPay4OxidPayment);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $bestitAmazonPay4OxidPayment = new bestitAmazonPay4Oxid_payment();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($bestitAmazonPay4OxidPayment, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::setPrimaryAmazonUserData()
     * @covers ::_managePrimaryUserData()
     * @covers ::_setObjectAmazonReferenceId()
     * @covers ::_getObjectAmazonReferenceId()
     * @throws Exception
     */
    public function testSetPrimaryAmazonUserData()
    {
        $oContainer = $this->_getContainerMock();

        // Utils
        $oUtils = $this->_getUtilsMock();
        $oUtils->expects($this->exactly(5))
            ->method('redirect')
            ->withConsecutive(
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=user&fnc=cleanAmazonPay', false),
                array('shopSecureHomeUrl?cl=payment', false),
                array('shopSecureHomeUrl?cl=payment', false),
                array('shopSecureHomeUrl?cl=payment', false)
            );

        $oContainer->expects($this->exactly(5))
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        // Config
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(5))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oConfig->expects($this->once())
            ->method('getShopId')
            ->will($this->returnValue(123));

        $oContainer->expects($this->exactly(6))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Client
        $aDefaultResponse = array(
            'GetOrderReferenceDetailsResult' => array(
                'OrderReferenceDetails' => array(
                    'OrderReferenceStatus' => array(
                        'State' => 'Draft'
                    ),
                    'Destination' => array(
                        'PhysicalDestination' => 'PhysicalDestinationValue'
                    )
                )
            )
        );
        $aNonDraftResponse = array(
            'GetOrderReferenceDetailsResult' => array(
                'OrderReferenceDetails' => array(
                    'OrderReferenceStatus' => array(
                        'State' => 'StateValue'
                    ),
                    'Destination' => array(
                        'PhysicalDestination' => 'PhysicalDestinationValue'
                    )
                )
            )
        );

        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(5))
            ->method('getOrderReferenceDetails')
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject($aNonDraftResponse),
                $this->_getResponseObject($aDefaultResponse),
                $this->_getResponseObject($aDefaultResponse),
                $this->_getResponseObject($aDefaultResponse)
            ));

        $oContainer->expects($this->exactly(5))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        // Session
        $oBasket = $this->_getBasketMock();
        $oBasket->expects($this->exactly(3))
            ->method('onUpdate');

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(3))
            ->method('getBasket')
            ->will($this->returnValue($oBasket));

        $oSession->expects($this->exactly(4))
            ->method('getVariable')
            ->withConsecutive(
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonOrderReferenceId'),
                array('amazonLoginToken')
            )
            ->will($this->onConsecutiveCalls(
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId',
                'amazonLoginToken'
            ));

        $oSession->expects($this->exactly(3))
            ->method('setVariable')
            ->withConsecutive(
                array('usr', 'newUserId'),
                array('blshowshipaddress', 1),
                array('deladrid', 'savedAddressId')
            );

        $oContainer->expects($this->exactly(6))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // ActiveUser
        $oUser = $this->_getUserMock();

        $oUser->expects($this->exactly(3))
            ->method('getId')
            ->will($this->returnValue('userId'));

        $oUser->expects($this->exactly(2))
            ->method('assign')
            ->withConsecutive(
                array(array(
                    'oxfname' => 'FirstNameValue',
                    'oxlname' => 'LastNameValue',
                    'oxcity' => 'CityValue',
                    'oxstateid' => 'StateOrRegionValue',
                    'oxcountryid' => 'CountryIdValue',
                    'oxzip' => 'PostalCodeValue',
                    'oxcompany' => 'CompanyNameValue',
                    'oxstreet' => 'StreetValue',
                    'oxstreetnr' => 'StreetNrValue',
                    'oxaddinfo' => 'AddInfoValue'
                )),
                array(array(
                    'oxfname' => 'FirstNameValue',
                    'oxlname' => 'LastNameValue',
                    'oxcity' => 'CityValue',
                    'oxstateid' => 'StateOrRegionValue',
                    'oxcountryid' => 'CountryIdValue',
                    'oxzip' => 'PostalCodeValue',
                    'oxcompany' => 'CompanyNameValue',
                    'oxfon' => 'PhoneValue',
                    'oxstreet' => 'StreetValue',
                    'oxstreetnr' => 'StreetNrValue',
                    'oxaddinfo' => 'AddInfoValue'
                ))
            );

        $oUser->expects($this->exactly(2))
            ->method('save');

        $oUser->expects($this->once())
            ->method('getFieldData')
            ->with('oxstreet')
            ->will($this->returnValue(''));

        $oFirstUserAddress = $this->getMock('oxAddress', array(), array(), '', false);
        $oFirstUserAddress->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('firstAddressId'));

        $oSecondUserAddress = $this->getMock('oxAddress', array(), array(), '', false);
        $oSecondUserAddress->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('secondAddressId'));

        $oUser->expects($this->once())
            ->method('getUserAddresses')
            ->will($this->returnValue(array($oFirstUserAddress, $oSecondUserAddress)));

        $oContainer->expects($this->exactly(3))
            ->method('getActiveUser')
            ->will($this->onConsecutiveCalls(
                false,
                $oUser,
                $oUser
            ));

        // AddressUtil
        $oAddressUtil = $this->_getAddressUtilMock();
        $oAddressUtil->expects($this->exactly(3))
            ->method('parseAmazonAddress')
            ->with('PhysicalDestinationValue')
            ->will($this->returnValue(array(
                'FirstName' => 'FirstNameValue',
                'LastName' => 'LastNameValue',
                'City' => 'CityValue',
                'StateOrRegion' => 'StateOrRegionValue',
                'CountryId' => 'CountryIdValue',
                'PostalCode' => 'PostalCodeValue',
                'CompanyName' => 'CompanyNameValue',
                'Phone' => 'PhoneValue',
                'Street' => 'StreetValue',
                'StreetNr' => 'StreetNrValue',
                'AddInfo' => 'AddInfoValue'
            )));

        $oContainer->expects($this->exactly(3))
            ->method('getAddressUtil')
            ->will($this->returnValue($oAddressUtil));

        // ObjectFactory
        $oNewUser = $this->_getUserMock();
        $oNewUser->expects($this->once())
            ->method('assign')
            ->with(array(
                'oxfname' => 'FirstNameValue',
                'oxlname' => 'LastNameValue',
                'oxcity' => 'CityValue',
                'oxstateid' => 'StateOrRegionValue',
                'oxcountryid' => 'CountryIdValue',
                'oxzip' => 'PostalCodeValue',
                'oxregister' => 0,
                'oxshopid' => 123,
                'oxactive' => 1,
                'oxusername' => 'orderReferenceId@amazon.com',
                'oxstreet' => 'StreetValue',
                'oxstreetnr' => 'StreetNrValue',
                'oxaddinfo' => 'AddInfoValue',
                'oxcompany' => 'CompanyNameValue'
            ));

        $oNewUser->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $oNewUser->expects($this->once())
            ->method('getId')
            ->will($this->returnValue('newUserId'));

        $oNewUser->expects($this->exactly(2))
            ->method('addToGroup')
            ->withConsecutive(array('oxidnewcustomer'), array('oxidnotyetordered'));

        $oAddress = $this->getMock('oxAddress', array(), array(), '', false);

        $oAddress->expects($this->once())
            ->method('assign')
            ->with(array(
                'oxfname' => 'FirstNameValue',
                'oxlname' => 'LastNameValue',
                'oxcity' => 'CityValue',
                'oxstateid' => 'StateOrRegionValue',
                'oxcountryid' => 'CountryIdValue',
                'oxzip' => 'PostalCodeValue',
                'oxuserid' => 'userId',
                'oxstreet' => 'StreetValue',
                'oxstreetnr' => 'StreetNrValue',
                'oxaddinfo' => 'AddInfoValue',
                'oxcompany' => 'CompanyNameValue'
            ));

        $oAddress->expects($this->once())
            ->method('save')
            ->will($this->returnValue('savedAddressId'));

        $oAddress->expects($this->once())
            ->method('load')
            ->with('secondAddressId');

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->withConsecutive(
                array('oxUser'),
                array('oxAddress')
            )
            ->will($this->onConsecutiveCalls(
                $oNewUser,
                $oAddress
            ));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        // Database
        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace(
                    "INSERT INTO `bestitamazonobject2reference` 
                    (`OXOBJECTID`, `AMAZONORDERREFERENCEID`) VALUES ('newUserId', 'orderReferenceId')
                    ON DUPLICATE KEY UPDATE OXOBJECTID = OXOBJECTID"
                )),
                array(new MatchIgnoreWhitespace(
                    "INSERT INTO `bestitamazonobject2reference` 
                    (`OXOBJECTID`, `AMAZONORDERREFERENCEID`) VALUES ('savedAddressId', 'orderReferenceId')
                    ON DUPLICATE KEY UPDATE OXOBJECTID = OXOBJECTID"
                ))
            );

        $oDatabase->expects($this->exactly(4))
            ->method('getOne')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace(
                    "SELECT `AMAZONORDERREFERENCEID` 
                    FROM `bestitamazonobject2reference`
                    WHERE `OXOBJECTID` = 'userId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT `AMAZONORDERREFERENCEID` 
                    FROM `bestitamazonobject2reference`
                    WHERE `OXOBJECTID` = 'userId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT `AMAZONORDERREFERENCEID` 
                    FROM `bestitamazonobject2reference`
                    WHERE `OXOBJECTID` = 'firstAddressId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT `AMAZONORDERREFERENCEID` 
                    FROM `bestitamazonobject2reference`
                    WHERE `OXOBJECTID` = 'secondAddressId'"
                ))
            )
            ->will($this->onConsecutiveCalls(
                'orderReferenceId',
                'otherOrderReferenceId',
                'otherOrderReferenceId',
                'orderReferenceId'
            ));

        $oContainer->expects($this->exactly(6))
            ->method('getDatabase')
            ->will($this->returnValue($oDatabase));

        $bestitAmazonPay4OxidPayment = $this->_getObject($oContainer);
        $bestitAmazonPay4OxidPayment->setPrimaryAmazonUserData();
        $bestitAmazonPay4OxidPayment->setPrimaryAmazonUserData();
        $bestitAmazonPay4OxidPayment->setPrimaryAmazonUserData();
        $bestitAmazonPay4OxidPayment->setPrimaryAmazonUserData();
        $bestitAmazonPay4OxidPayment->setPrimaryAmazonUserData();
    }

    /**
     * @group unit
     * @covers ::validatePayment()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testValidatePayment()
    {
        $oContainer = $this->_getContainerMock();

        // Session
        $oSession = $this->_getSessionMock();

        $oSession->expects($this->exactly(4))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls(
                '',
                'orderReferenceId',
                'orderReferenceId',
                'orderReferenceId'
            ));

        $oSession->expects($this->once())
            ->method('setVariable')
            ->with('ordrem', 'remark');

        $oSession->expects($this->once())
            ->method('deleteVariable')
            ->with('ordrem');

        $oContainer->expects($this->exactly(4))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Config
        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(5))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('paymentid'),
                array('paymentid'),
                array('order_remark'),
                array('paymentid'),
                array('order_remark')
            )
            ->will($this->onConsecutiveCalls(
                'some',
                'bestitamazon',
                'remark',
                'bestitamazon',
                null
            ));

        $oContainer->expects($this->exactly(4))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $bestitAmazonPay4OxidPayment = $this->_getObject($oContainer);
        $bestitAmazonPay4OxidPayment->validatePayment();
        $bestitAmazonPay4OxidPayment->validatePayment();
        $bestitAmazonPay4OxidPayment->validatePayment();
        $bestitAmazonPay4OxidPayment->validatePayment();
    }

    /**
     * @group unit
     * @covers ::getOrderRemark()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testGetOrderRemark()
    {
        $oContainer = $this->_getContainerMock();

        // Session
        $oSession = $this->_getSessionMock();

        $oSession->expects($this->exactly(1))
            ->method('getVariable')
            ->with('ordrem')
            ->will($this->returnValue('sessionRemark'));

        $oContainer->expects($this->exactly(1))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // Config
        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(2))
            ->method('getRequestParameter')
            ->with('order_remark')
            ->will($this->onConsecutiveCalls(
                null,
                'paramRemark'
            ));

        $oConfig->expects($this->exactly(2))
            ->method('checkParamSpecialChars')
            ->withConsecutive(
                array('paramRemark'),
                array('sessionRemark')
            )
            ->will($this->returnValue('processedRemark'));

        $oContainer->expects($this->exactly(4))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oContainer->expects($this->exactly(3))
            ->method('getActiveUser')
            ->will($this->onConsecutiveCalls(false, false, $this->_getUserMock()));

        $bestitAmazonPay4OxidPayment = $this->_getObject($oContainer);
        self::assertFalse($bestitAmazonPay4OxidPayment->getOrderRemark());
        self::assertEquals('processedRemark', $bestitAmazonPay4OxidPayment->getOrderRemark());
        self::assertEquals('processedRemark', $bestitAmazonPay4OxidPayment->getOrderRemark());
    }
}
