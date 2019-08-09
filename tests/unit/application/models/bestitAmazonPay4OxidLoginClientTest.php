<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';


/**
 * Unit test for class bestitAmazonPay4OxidLoginClient
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4OxidLoginClient
 */
class bestitAmazonPay4OxidLoginClientTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param oxUser|bool                       $oUser
     * @param bestitAmazonPay4OxidClient        $oClient
     * @param bestitAmazonPay4Oxid              $oModule
     * @param oxConfig                          $oConfig
     * @param DatabaseInterface                 $oDatabase
     * @param oxLang                            $oLanguage
     * @param oxSession                         $oSession
     * @param oxUtilsServer                     $oUtilsServer
     * @param bestitAmazonPay4OxidAddressUtil   $oAddressUtil
     * @param bestitAmazonPay4OxidObjectFactory $objectFactory
     *
     * @return bestitAmazonPay4OxidLoginClient
     * @throws ReflectionException
     */
    private function _getObject(
        $oUser,
        bestitAmazonPay4OxidClient $oClient,
        bestitAmazonPay4Oxid $oModule,
        oxConfig $oConfig,
        DatabaseInterface $oDatabase,
        oxLang $oLanguage,
        oxSession $oSession,
        oxUtilsServer $oUtilsServer,
        bestitAmazonPay4OxidAddressUtil $oAddressUtil,
        bestitAmazonPay4OxidObjectFactory $objectFactory
    ) {
        $oBestitAmazonPay4OxidLoginClient = new bestitAmazonPay4OxidLoginClient();
        $oBestitAmazonPay4OxidLoginClient->setLogger(new NullLogger());
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oActiveUserObject', $oUser);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oClientObject', $oClient);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oModuleObject', $oModule);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oConfigObject', $oConfig);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oDatabaseObject', $oDatabase);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oLanguageObject', $oLanguage);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oSessionObject', $oSession);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oUtilsServerObject', $oUtilsServer);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oAddressUtilObject', $oAddressUtil);
        self::setValue($oBestitAmazonPay4OxidLoginClient, '_oObjectFactory', $objectFactory);

        return $oBestitAmazonPay4OxidLoginClient;
    }

    /**
     * @group unit
     * @covers ::getInstance()
     * @throws oxSystemComponentException
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidLoginClient = new bestitAmazonPay4OxidLoginClient();
        $oBestitAmazonPay4OxidLoginClient->setLogger(new NullLogger());
        self::assertInstanceOf('bestitAmazonPay4OxidLoginClient', $oBestitAmazonPay4OxidLoginClient);
        self::assertInstanceOf('bestitAmazonPay4OxidLoginClient', bestitAmazonPay4OxidLoginClient::getInstance());
    }

    /**
     * @group unit
     * @covers ::isActive()
     * @throws ReflectionException
     */
    public function testIsActive()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(12))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blAmazonLoginActive'),
                array('sAmazonLoginClientId'),
                array('sAmazonSellerId'),
                array('blAmazonLoginActive'),
                array('sAmazonLoginClientId'),
                array('sAmazonSellerId'),
                array('blAmazonLoginActive'),
                array('sAmazonLoginClientId'),
                array('sAmazonSellerId'),
                array('blAmazonLoginActive'),
                array('sAmazonLoginClientId'),
                array('sAmazonSellerId')
            )
            ->will($this->onConsecutiveCalls(
                false,
                'clientId',
                'sellerId',
                true,
                '',
                'sellerId',
                true,
                'clientId',
                '',
                true,
                'clientId',
                'sellerId'
            ));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::assertFalse($oLoginClient->isActive());

        self::setValue($oLoginClient, '_isActive', null);
        self::assertFalse($oLoginClient->isActive());

        self::setValue($oLoginClient, '_isActive', null);
        self::assertFalse($oLoginClient->isActive());
        self::assertFalse($oLoginClient->isActive());

        self::setValue($oLoginClient, '_isActive', null);
        self::assertTrue($oLoginClient->isActive());
    }

    /**
     * @group unit
     * @covers ::showAmazonLoginButton()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testShowAmazonLoginButton()
    {
        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(5))
            ->method('isSsl')
            ->will($this->onConsecutiveCalls(false, true, true, false, true));

        $oConfig->expects($this->exactly(5))
            ->method('getRequestParameter')
            ->will($this->onConsecutiveCalls('basket', 'user', 'some', 'basket', 'some'));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::setValue($oLoginClient, '_isActive', false);
        self::assertFalse($oLoginClient->showAmazonLoginButton());

        self::setValue($oLoginClient, '_isActive', true);
        self::assertFalse($oLoginClient->showAmazonLoginButton());

        self::assertFalse($oLoginClient->showAmazonLoginButton());

        self::setValue($oLoginClient, '_oActiveUserObject', false);
        self::assertFalse($oLoginClient->showAmazonLoginButton());

        self::assertTrue($oLoginClient->showAmazonLoginButton());
    }

    /**
     * @group unit
     * @covers ::showAmazonPayButton()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testShowAmazonPayButton()
    {
        $oModule = $this->_getModuleMock();
        $oModule->expects($this->exactly(5))
            ->method('isActive')
            ->will($this->onConsecutiveCalls(false, false, true, false, true));

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(5))
            ->method('isSsl')
            ->will($this->onConsecutiveCalls(false, true, false, true, true));

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(5))
            ->method('getVariable')
            ->with('amazonOrderReferenceId')
            ->will($this->onConsecutiveCalls('referenceId', null, null, null, null));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $oModule,
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getLanguageMock(),
            $oSession,
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::setValue($oLoginClient, '_isActive', false);
        self::assertFalse($oLoginClient->showAmazonPayButton());

        self::setValue($oLoginClient, '_isActive', true);
        self::assertFalse($oLoginClient->showAmazonPayButton());

        self::assertFalse($oLoginClient->showAmazonPayButton());
        self::assertFalse($oLoginClient->showAmazonPayButton());
        self::assertTrue($oLoginClient->showAmazonPayButton());
    }

    /**
     * @group unit
     * @covers ::processAmazonLogin()
     * @throws Exception
     */
    public function testProcessAmazonLogin()
    {
        $oClient = $this->_getClientMock();
        $oClient->expects($this->once())
            ->method('processAmazonLogin')
            ->with('accessToken')
            ->will($this->returnValue('processAmazonLoginResult'));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $oClient,
            $this->_getModuleMock(),
            $this->_getConfigMock(),
            $this->_getDatabaseMock(),
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::assertEquals('processAmazonLoginResult', $oLoginClient->processAmazonLogin('accessToken'));
    }

    /**
     * @group unit
     * @covers ::amazonUserIdExists()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testAmazonUserIdExists()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('getShopId')
            ->will($this->returnValue(123));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(2))
            ->method('quote')
            ->withConsecutive(array(321), array(123))
            ->will($this->onConsecutiveCalls('\'321\'', '\'123\''));
        $oDatabase->expects($this->once())
            ->method('getOne')
            ->with(new MatchIgnoreWhitespace(
                "SELECT OXID
                FROM oxuser
                WHERE BESTITAMAZONID= '321'
                  AND OXSHOPID = '123'
                  AND OXACTIVE = 1"
            ))
            ->will($this->returnValue('result'));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $oDatabase,
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        $oUserData = new stdClass();
        $oUserData->user_id = 321;

        self::assertEquals('result', $oLoginClient->amazonUserIdExists($oUserData));
    }

    /**
     * @group unit
     * @covers ::oxidUserExists()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testOxidUserExists()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('getShopId')
            ->will($this->returnValue(123));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(2))
            ->method('quote')
            ->withConsecutive(array('mail'), array(123))
            ->will($this->onConsecutiveCalls('\'mail\'', '\'123\''));
        $oDatabase->expects($this->once())
            ->method('getRow')
            ->with(new MatchIgnoreWhitespace(
                "SELECT *
                FROM oxuser
                WHERE OXUSERNAME = 'mail'
                  AND OXSHOPID = '123'"
            ))
            ->will($this->returnValue(array('a' => 'aV', 'b' => 'bV')));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $oDatabase,
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        $oUserData = new stdClass();
        $oUserData->email = 'mail';

        self::assertEquals(array('a' => 'aV', 'b' => 'bV'), $oLoginClient->oxidUserExists($oUserData));
    }

    /**
     * @group unit
     * @covers ::createOxidUser()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testCreateOxidUser()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('getShopId')
            ->will($this->returnValue(123));

        $oAddressUtil = $this->_getAddressUtilMock();
        $oAddressUtil->expects($this->exactly(2))
            ->method('encodeString')
            ->withConsecutive(array('FName MName'), array('LName'))
            ->will($this->onConsecutiveCalls('FName MName Encoded', 'LName Encoded'));

        $oUser = $this->_getUserMock();
        $oUser->expects($this->once())
            ->method('assign')
            ->with(array(
                'oxregister' => 0,
                'oxshopid' => 123,
                'oxactive' => 1,
                'oxusername' => 'mail',
                'oxfname' => 'FName MName Encoded',
                'oxlname' => 'LName Encoded',
                'bestitamazonid' => 'amazonUserId'
            ));

        $oUser->expects($this->once())
            ->method('setPassword')
            ->with($this->matchesRegularExpression('/[a-z0-9]{8}/'));

        $oUser->expects($this->once())
            ->method('save')
            ->will($this->returnValue(true));

        $oUser->expects($this->exactly(2))
            ->method('addToGroup')
            ->withConsecutive(array('oxidnewcustomer'), array('oxidnotyetordered'));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->once())
            ->method('createOxidObject')
            ->with('oxUser')
            ->will($this->returnValue($oUser));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $oAddressUtil,
            $oObjectFactory
        );

        $oUserData = new stdClass();
        $oUserData->user_id = 'amazonUserId';
        $oUserData->name = 'FName MName LName';
        $oUserData->email = 'mail';

        self::assertTrue($oLoginClient->createOxidUser($oUserData));
    }

    /**
     * @group unit
     * @covers ::deleteUser()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testDeleteUser()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->once())
            ->method('getShopId')
            ->will($this->returnValue(123));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(2))
            ->method('quote')
            ->withConsecutive(array(321), array(123))
            ->will($this->onConsecutiveCalls('\'321\'', '\'123\''));
        $oDatabase->expects($this->once())
            ->method('execute')
            ->with(new MatchIgnoreWhitespace(
                "DELETE FROM oxuser
                WHERE OXID = '321'
                  AND OXSHOPID = '123'"
            ))
            ->will($this->returnValue(array('a' => 'aV', 'b' => 'bV')));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $oDatabase,
            $this->_getLanguageMock(),
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        $oUserData = new stdClass();
        $oUserData->email = 'mail';

        self::assertEquals(array('a' => 'aV', 'b' => 'bV'), $oLoginClient->deleteUser('321'));
    }

    /**
     * @group unit
     * @covers ::cleanAmazonPay()
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testCleanAmazonPay()
    {
        $oModule = $this->_getModuleMock();
        $oModule->expects($this->once())
            ->method('cleanAmazonPay');

        $oSession = $this->_getSessionMock();
        $oSession->expects($this->once())
            ->method('deleteVariable')
            ->with('amazonLoginToken');

        $oUtilsSever = $this->_getUtilsServerMock();
        $oUtilsSever->expects($this->once())
            ->method('setOxCookie')
            ->with('amazon_Login_state_cache', '', time() - 3600, '/');

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $oModule,
            $this->_getConfigMock(),
            $this->_getDatabaseMock(),
            $this->_getLanguageMock(),
            $oSession,
            $oUtilsSever,
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        $oLoginClient->cleanAmazonPay();
    }

    /**
     * @group unit
     * @covers ::getAmazonLanguage()
     * @throws ReflectionException
     */
    public function testGetAmazonLanguage()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(2))
            ->method('getConfigParam')
            ->with('aAmazonLanguages')
            ->will($this->returnValue(array(
                'de' => 'de_DE'
            )));

        $oLanguage = $this->_getLanguageMock();
        $oLanguage->expects($this->exactly(2))
            ->method('getLanguageAbbr')
            ->will($this->onConsecutiveCalls('some', 'de'));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $oLanguage,
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::assertEquals('some', $oLoginClient->getAmazonLanguage());
        self::assertEquals('de_DE', $oLoginClient->getAmazonLanguage());
    }

    /**
     * @group unit
     * @covers ::getLangIdByAmazonLanguage()
     * @throws ReflectionException
     */
    public function testGetLangIdByAmazonLanguage()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(4))
            ->method('getConfigParam')
            ->with('aAmazonLanguages')
            ->will($this->returnValue(array(
                'de' => 'de_DE',
                'fr' => 'fr_FR',
                'en' => 'en_EN',
            )));

        $oLanguage = $this->_getLanguageMock();
        $oLanguage->expects($this->exactly(4))
            ->method('getAllShopLanguageIds')
            ->will($this->returnValue(array(
                1 => 'de',
                3 => 'en'
            )));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $this->_getClientMock(),
            $this->_getModuleMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $oLanguage,
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::assertFalse($oLoginClient->getLangIdByAmazonLanguage('some'));
        self::assertEquals(1, $oLoginClient->getLangIdByAmazonLanguage('de_DE'));
        self::assertFalse($oLoginClient->getLangIdByAmazonLanguage('fr_FR'));
        self::assertEquals(3, $oLoginClient->getLangIdByAmazonLanguage('en_EN'));
    }

    /**
     * @group unit
     * @covers ::getOrderLanguageId()
     * @throws Exception
     */
    public function testGetOrderLanguageId()
    {
        $oOrder = $this->_getOrderMock();
        $oOrder->expects($this->exactly(2))
            ->method('getFieldData')
            ->with('oxlang')
            ->willReturn(123);

        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(3))
            ->method('getOrderReferenceDetails')
            ->with($oOrder, array(), true)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderLanguage' => 'some'
                        )
                    )
                )),
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderLanguage' => 'en_EN'
                        )
                    )
                ))
            ));

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(2))
            ->method('getConfigParam')
            ->with('aAmazonLanguages')
            ->will($this->returnValue(array(
                'de' => 'de_DE',
                'fr' => 'fr_FR',
                'en' => 'en_EN',
            )));

        $oLanguage = $this->_getLanguageMock();
        $oLanguage->expects($this->exactly(2))
            ->method('getAllShopLanguageIds')
            ->will($this->returnValue(array(
                1 => 'de',
                3 => 'en'
            )));

        $oLoginClient = $this->_getObject(
            $this->_getUserMock(),
            $oClient,
            $this->_getModuleMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $oLanguage,
            $this->_getSessionMock(),
            $this->_getUtilsServerMock(),
            $this->_getAddressUtilMock(),
            $this->_getObjectFactoryMock()
        );

        self::assertEquals(123, $oLoginClient->getOrderLanguageId($oOrder));
        self::assertEquals(123, $oLoginClient->getOrderLanguageId($oOrder));
        self::assertEquals(3, $oLoginClient->getOrderLanguageId($oOrder));
    }
}
