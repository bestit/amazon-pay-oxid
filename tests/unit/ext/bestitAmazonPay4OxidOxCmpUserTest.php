<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_oxcmp_user
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_oxcmp_user
 */
class bestitAmazonPay4OxidOxCmpUserTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxcmp_user
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOxCmpUser = new bestitAmazonPay4Oxid_oxcmp_user();
        self::setValue($oBestitAmazonPay4OxidOxCmpUser, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidOxCmpUser;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOxCmpUser = new bestitAmazonPay4Oxid_oxcmp_user();
        self::assertInstanceOf('bestitAmazonPay4Oxid_oxcmp_user', $oBestitAmazonPay4OxidOxCmpUser);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOxCmpUser = new bestitAmazonPay4Oxid_oxcmp_user();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOxCmpUser, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::amazonLogin()
     * @covers ::_setErrorAndRedirect()
     * @throws Exception
     * @throws ReflectionException
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function testAmazonLogin()
    {
        $oContainer = $this->_getContainerMock();

        $oUser = $this->_getUserMock();
        $oUser->expects($this->once())
            ->method('assign')
            ->with(array('bestitamazonid' => 'userId'));
        $oUser->expects($this->once())
            ->method('save');

        $oContainer->expects($this->exactly(4))
            ->method('getActiveUser')
            ->will($this->onConsecutiveCalls(
                $oUser,
                false,
                false,
                false
            ));

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(18))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('access_token'),
                array('access_token'),
                array('access_token'),
                array('access_token'),
                array('amazonOrderReferenceId'),
                array('redirectCl'),
                array('access_token'),
                array('amazonOrderReferenceId'),
                array('redirectCl'),
                array('access_token'),
                array('amazonOrderReferenceId'),
                array('redirectCl'),
                array('access_token'),
                array('amazonOrderReferenceId'),
                array('redirectCl'),
                array('access_token'),
                array('amazonOrderReferenceId'),
                array('redirectCl')
            )
            ->will($this->onConsecutiveCalls(
                null,
                'token',
                'token',
                'token',
                null,
                null,
               'token',
                'orderReferenceId',
                'redirectClValue',
                'token',
                null,
                null,
                'token',
                null,
                null,
                'token',
                null,
                null
            ));

        $oConfig->expects($this->exactly(9))
            ->method('getShopSecureHomeUrl')
            ->will($this->returnValue('shopSecureHomeUrl?'));

        $oContainer->expects($this->exactly(8))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        // Session
        $oSession = $this->_getSessionMock();
        $oSession->expects($this->exactly(10))
            ->method('setVariable')
            ->withConsecutive(
                array('amazonLoginToken', 'token'),
                array('amazonLoginToken', 'token'),
                array('amazonLoginToken', 'token'),
                array('usr', 'existingUserId'),
                array('amazonLoginToken', 'token'),
                array('amazonOrderReferenceId', 'orderReferenceId'),
                array('amazonLoginToken', 'token'),
                array('amazonLoginToken', 'token'),
                array('usr', 'newUserId'),
                array('amazonLoginToken', 'token')
            );

        $oContainer->expects($this->exactly(7))
            ->method('getSession')
            ->will($this->returnValue($oSession));

        // LoginClient
        $oDefaultResponse = $this->_getResponseObject(array('user_id' => 'userId'));

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->exactly(7))
            ->method('processAmazonLogin')
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(array()),
                $this->_getResponseObject(array('error' => 'someError')),
                $oDefaultResponse,
                $oDefaultResponse,
                $oDefaultResponse,
                $oDefaultResponse,
                $oDefaultResponse
            ));


        $oLoginClient->expects($this->exactly(5))
            ->method('amazonUserIdExists')
            ->with($oDefaultResponse)
            ->will($this->onConsecutiveCalls(
                'existingUserId',
                false,
                false,
                false,
                false
            ));

        $oLoginClient->expects($this->exactly(3))
            ->method('oxidUserExists')
            ->with($oDefaultResponse)
            ->will($this->onConsecutiveCalls(
                array('OXPASSWORD' => 'some'),
                array('OXID' => 'someId'),
                array()
            ));

        $oLoginClient->expects($this->once())
            ->method('cleanAmazonPay');

        $oLoginClient->expects($this->once())
            ->method('deleteUser')
            ->with('someId');

        $oLoginClient->expects($this->exactly(2))
            ->method('createOxidUser')
            ->with($oDefaultResponse)
            ->will($this->onConsecutiveCalls('newUserId', false));

        $oContainer->expects($this->exactly(7))
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        // ObjectFactory
        $oUserException = $this->getMock('oxUserException');
        $oUserException->expects($this->exactly(3))
            ->method('setMessage')
            ->withConsecutive(
                array('BESTITAMAZONPAYLOGIN_ERROR_UNEXPECTED'),
                array('BESTITAMAZONPAYLOGIN_ERROR_someError'),
                array('BESTITAMAZONPAYLOGIN_ERROR_ACCOUNT_WITH_EMAIL_EXISTS')
            );

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(3))
            ->method('createOxidObject')
            ->with('oxUserException')
            ->will($this->returnValue($oUserException));

        $oContainer->expects($this->exactly(3))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        // UtilsView
        $oUtilsView = $this->_getUtilsViewMock();
        $oUtilsView->expects($this->exactly(3))
            ->method('addErrorToDisplay')
            ->with($oUserException, false, true);

        $oContainer->expects($this->exactly(3))
            ->method('getUtilsView')
            ->will($this->returnValue($oUtilsView));

        // Utils
        $oUtils = $this->_getUtilsMock();
        $oUtils->expects($this->exactly(6))
            ->method('redirect')
            ->withConsecutive(
                array('shopSecureHomeUrl?cl=account_user', false),
                array('shopSecureHomeUrl?cl=account_user', false),
                array('shopSecureHomeUrl?cl=account_user', false),
                array('shopSecureHomeUrl?cl=redirectClValue', false),
                array('shopSecureHomeUrl?cl=account_user', false),
                array('shopSecureHomeUrl?cl=account_user', false)
            );

        $oContainer->expects($this->exactly(8))
            ->method('getUtils')
            ->will($this->returnValue($oUtils));

        $oBestitAmazonPay4OxidOxCmpUser = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
        $oBestitAmazonPay4OxidOxCmpUser->amazonLogin();
    }

    /**
     * @group unit
     * @covers ::_afterLogout()
     * @throws ReflectionException
     */
    public function testAfterLogout()
    {
        $oContainer = $this->_getContainerMock();

        $oLoginClient = $this->_getLoginClientMock();
        $oLoginClient->expects($this->once())
            ->method('cleanAmazonPay');

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oBestitAmazonPay4OxidOxCmpUser = $this->_getObject($oContainer);
        self::callMethod($oBestitAmazonPay4OxidOxCmpUser, '_afterLogout');
    }
}
