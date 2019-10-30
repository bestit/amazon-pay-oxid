<?php

use Psr\Log\LoggerInterface;

/**
 * Extension for OXID oxcmp_user component
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_oxcmp_user extends bestitAmazonPay4Oxid_oxcmp_user_parent
{
    /**
     * @var null|bestitAmazonPay4OxidContainer
     */
    protected $_oContainer = null;

    /**
     * The logger
     *
     * @var LoggerInterface
     */
    protected $_oLogger;

    /**
     * bestitAmazonPay4Oxid_oxcmp_user constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_oLogger = $this->_getContainer()->getLogger();
    }

    /**
     * Returns the active user object.
     *
     * @return bestitAmazonPay4OxidContainer
     * @throws oxSystemComponentException
     */
    protected function _getContainer()
    {
        if ($this->_oContainer === null) {
            $this->_oContainer = oxNew('bestitAmazonPay4OxidContainer');
        }

        return $this->_oContainer;
    }

    /**
     * @param string $sError
     * @param string $sRedirectUrl
     *
     * @throws oxSystemComponentException
     * @throws oxSystemComponentException
     * @throws oxSystemComponentException
     */
    protected function _setErrorAndRedirect($sError, $sRedirectUrl)
    {
        $this->_oLogger->debug(
            'Redirect customer',
            array('error' => $sError, 'redirectUrl' => $sRedirectUrl)
        );

        /** @var oxUserException $oEx */
        $oEx = $this->_getContainer()->getObjectFactory()->createOxidObject('oxUserException');
        $oEx->setMessage($sError);
        $this->_getContainer()->getUtilsView()->addErrorToDisplay($oEx, false, true);
        $this->_getContainer()->getUtils()->redirect($sRedirectUrl, false);
    }

    /**
     * Execute the Amazon login
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     * @throws Exception
     *
     * @return void
     */
    public function amazonLogin()
    {
        //If we have no token we have nothing to do here
        $oConfig = $this->_getContainer()->getConfig();
        $sAccessToken = (string)$oConfig->getRequestParameter('access_token');

        $this->_oLogger->debug(
            'Handle amazon login',
            array('hasToken' => !empty($sAccessToken))
        );

        if ($sAccessToken === '') {
            $this->_oLogger->debug(
                'No token found, abort'
            );
            return;
        }

        $sRedirectUrl = $oConfig->getShopSecureHomeUrl().'cl=account_user';
        $oSession = $this->_getContainer()->getSession();
        $oLoginClient = $this->_getContainer()->getLoginClient();
        //Get user data from Token
        $oSession->setVariable('amazonLoginToken', $sAccessToken);
        $oUserData = $oLoginClient->processAmazonLogin($sAccessToken);

        //Error handling: If we don't have user ID output error and redirect to login page
        if (empty($oUserData->user_id)) {
            $sError = ($oUserData->error) ? 'BESTITAMAZONPAYLOGIN_ERROR_'.$oUserData->error
                : 'BESTITAMAZONPAYLOGIN_ERROR_UNEXPECTED';

            $this->_oLogger->error(
                'No id in user found',
                array('error' => $sError)
            );

            $this->_setErrorAndRedirect($sError, $sRedirectUrl);
            return;
        }

        //Set Amazon reference ID to session
        $sOrderReferenceId = (string)$oConfig->getRequestParameter('amazonOrderReferenceId');

        if ($sOrderReferenceId !== '') {
            $oSession->setVariable('amazonOrderReferenceId', $sOrderReferenceId);
        }

        //Redirect url
        $sRedirectClass = (string)$oConfig->getRequestParameter('redirectCl');

        if ($sRedirectClass !== '') {
            $sRedirectUrl = $oConfig->getShopSecureHomeUrl().'cl='.$sRedirectClass;
        }

        $oUtils = $this->_getContainer()->getUtils();

        //If OXID user with Amazon User id exists login User by Amazon User Id
        if ($sUserId = $oLoginClient->amazonUserIdExists($oUserData)) {
            $this->_oLogger->debug(
                'Oxid user with user id exists, login user by amazon user id',
                array('amazonUserId' => $sUserId)
            );

            $oSession->setVariable('usr', $sUserId);
            $oUtils->redirect($sRedirectUrl, false);
            return;
        }

        //If OXID user is logged in and he has logged in also with Amazon for the first time
        if ($oUser = $this->_getContainer()->getActiveUser()) {
            $this->_oLogger->debug(
                'oxid user already logged in and new amazon customer detected',
                array('amazonUserId' => $oUserData->user_id)
            );

            $oUser->assign(array('bestitamazonid' => $oUserData->user_id));
            $oUser->save();
            $oUtils->redirect($sRedirectUrl, false);
            return;
        }

        //If OXID user with Amazon user id does not exists, check if OXID User with email from Amazon exists
        //And If user exists and has a
        $aUserData = $oLoginClient->oxidUserExists($oUserData);

        if ($aUserData['OXPASSWORD']) {
            $this->_oLogger->error(
                'oxid user with email already exists',
                array('amazonUserId' => $oUserData->user_id)
            );

            $oLoginClient->cleanAmazonPay();
            $this->_setErrorAndRedirect(
                'BESTITAMAZONPAYLOGIN_ERROR_ACCOUNT_WITH_EMAIL_EXISTS',
                $oConfig->getShopSecureHomeUrl().'cl=account_user'
            );
            return;
        } elseif ($aUserData['OXID']) {
            $oLoginClient->deleteUser($aUserData['OXID']);
        }

        //If OXID user with Amazon user id does not exists and OXID User with email from Amazon does not exists
        //Attempt to create new user and to login it
        if ($sUserId = $oLoginClient->createOxidUser($oUserData)) {
            $this->_oLogger->debug(
                'Attempt to create new user and login',
                array('oxId' => $sUserId)
            );

            $oSession->setVariable('usr', $sUserId);
            $oUtils->redirect($sRedirectUrl, false);
            return;
        }
    }


    /**
     * Deletes Amazon User data
     *
     * @return null
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _afterLogout()
    {
        $this->_getContainer()->getLoginClient()->cleanAmazonPay();
        return parent::_afterLogout();
    }
}
