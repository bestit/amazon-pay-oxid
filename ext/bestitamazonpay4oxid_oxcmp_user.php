<?php

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
        /** @var oxUserException $oEx */
        $oEx = $this->_getContainer()->getObjectFactory()->createOxidObject('oxUserException');
        $oEx->setMessage($sError);
        $this->_getContainer()->getUtilsView()->addErrorToDisplay($oEx, false, true);
        $this->_getContainer()->getUtils()->redirect($sRedirectUrl, false);
    }

    /**
     * Amazon login
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     * @throws Exception
     */
    public function amazonLogin()
    {
        //If we have no token we have nothing to do here
        $oConfig = $this->_getContainer()->getConfig();
        $sAccessToken = (string)$oConfig->getRequestParameter('access_token');
        
        if ($sAccessToken === '') {
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
            $oSession->setVariable('usr', $sUserId);
            $oUtils->redirect($sRedirectUrl, false);
            return;
        }

        //If OXID user is logged in and he has logged in also with Amazon for the first time
        if ($oUser = $this->_getContainer()->getActiveUser()) {
            $oUser->assign(array('bestitamazonid' => $oUserData->user_id));
            $oUser->save();
            $oUtils->redirect($sRedirectUrl, false);
            return;
        }

        //If OXID user with Amazon user id does not exists, check if OXID User with email from Amazon exists
        //And If user exists and has a
        $aUserData = $oLoginClient->oxidUserExists($oUserData);

        if ($aUserData['OXPASSWORD']) {
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