<?php

/**
 * Model for Login with Amazon
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidLoginClient extends bestitAmazonPay4OxidContainer
{
    /**
     * @var bestitAmazonPay4OxidLoginClient
     */
    private static $_instance = null;

    /**
     * @var null|bool
     */
    protected $_isActive = null;

    /**
     * Singleton instance
     *
     * @return bestitAmazonPay4OxidLoginClient
     * @throws oxSystemComponentException
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = oxNew(__CLASS__);
        }

        return self::$_instance;
    }

    /**
     * Method checks if Amazon Login is active and can be used
     *
     * @return bool
     */
    public function isActive()
    {
        $loginActive = null;
        $clientId = null;
        $sellerId = null;

        if ($this->_isActive === null) {
            $loginActive = ((bool)$this->getConfig()->getConfigParam('blAmazonLoginActive') === true);
            $clientId = ((string)$this->getConfig()->getConfigParam('sAmazonLoginClientId') !== '');
            $sellerId = ((string)$this->getConfig()->getConfigParam('sAmazonSellerId') !== '');
            //Checkbox for active Login checked
            $this->_isActive = ($loginActive && $clientId && $sellerId);
        }

        $this->getLogger()->debug(
            'Check if amazon pay is active',
            array(
                'result' => $this->_isActive,
                'loginActive' => $loginActive,
                'clientId' => $clientId,
                'sellerId' => $sellerId,
            )
        );

        return $this->_isActive;
    }

    /**
     * Method checks if Amazon Login button can be showed
     *
     * @return bool
     * @throws oxSystemComponentException
     */
    public function showAmazonLoginButton()
    {
        $requestParameter = (string) $this->getConfig()->getRequestParameter('cl');
        $isActive = $this->isActive() === true;
        $isSSL = $this->getConfig()->isSsl() === true;
        $isUserNotActive = $this->getActiveUser() === false;
        $isNotBasket = $requestParameter !== 'basket';
        $isNotUser = $requestParameter !== 'user';

        $result = ($isActive && $isSSL && $isUserNotActive && $isNotBasket && $isNotUser);

        $this->getLogger()->debug(
            'Check if amazon login button should be shown',
            array(
                'result' => $result,
                'isActive' => $isActive,
                'isSSL' => $isSSL,
                'isUserNotActive' => $isUserNotActive,
                'isNotBasket' => $isNotBasket,
                'isNotUser' => $isNotUser
            )
        );

        return $result;
    }

    /**
     * Method checks if Amazon Login button can be showed
     *
     * @return bool
     * @throws oxConnectionException
     */
    public function showAmazonPayButton()
    {
        $isActive = $this->isActive() === true;
        $isSSL = $this->getConfig()->isSsl() === true;
        $isModuleActive = $this->getModule()->isActive() === true;
        $hasEmptyReferenceId = (string)$this->getSession()->getVariable('amazonOrderReferenceId') === '';

        $result = ($isActive && $isSSL && $isModuleActive && $hasEmptyReferenceId);

        $this->getLogger()->debug(
            'Check if amazon button should be shown',
            array(
                'result' => $result,
                'isActive' => $isActive,
                'isSSL' => $isSSL,
                'isModuleActive' => $isModuleActive,
                'hasEmptyReferenceId' => $hasEmptyReferenceId
            )
        );

        return $result;
    }

    /**
     * Returns Response from Amazon for a given access token
     *
     * @param string $sAccessToken Access token
     *
     * @return object
     * @throws Exception
     */
    public function processAmazonLogin($sAccessToken)
    {
        return json_decode(json_encode($this->getClient()->processAmazonLogin($sAccessToken)));
    }

    /**
     * Check if user with Amazon User Id exists
     *
     * @param stdClass $oUserData The oxid user data
     *
     * @return boolean
     * @throws oxConnectionException
     */
    public function amazonUserIdExists($oUserData)
    {
        $sSql = "SELECT OXID
            FROM oxuser
            WHERE BESTITAMAZONID= {$this->getDatabase()->quote($oUserData->user_id)}
              AND OXSHOPID = {$this->getDatabase()->quote($this->getConfig()->getShopId())}
              AND OXACTIVE = 1";

        return $this->getDatabase()->getOne($sSql);
    }

    /**
     * Check if user with Email from Amazon exists
     *
     * @param stdClass $oUserData The oxid user data
     *
     * @return array
     * @throws oxConnectionException
     */
    public function oxidUserExists($oUserData)
    {
        $sSql = "SELECT *
            FROM oxuser
            WHERE OXUSERNAME = {$this->getDatabase()->quote($oUserData->email)}
              AND OXSHOPID = {$this->getDatabase()->quote($this->getConfig()->getShopId())}";

        return $this->getDatabase()->getRow($sSql);
    }

    /**
     * Create new oxid user with details from Amazon
     *
     * @param stdClass $oUserData The oxid user data
     *
     * @return boolean
     * @throws oxSystemComponentException
     */
    public function createOxidUser($oUserData)
    {
        $aFullName = explode(' ', trim($oUserData->name));
        $sLastName = array_pop($aFullName);
        $sFirstName = implode(' ', $aFullName);

        /** @var oxUser $oUser */
        $oUser = $this->getObjectFactory()->createOxidObject('oxUser');
        $oUser->assign(array(
            'oxregister' => 0,
            'oxshopid' => $this->getConfig()->getShopId(),
            'oxactive' => 1,
            'oxusername' => $oUserData->email,
            'oxfname' => $this->getAddressUtil()->encodeString($sFirstName),
            'oxlname' => $this->getAddressUtil()->encodeString($sLastName),
            'bestitamazonid' => $oUserData->user_id
        ));

        //Set user random password just to have it
        $sNewPass = substr(md5(time() . rand(0, 5000)), 0, 8);
        $oUser->setPassword($sNewPass);

        //Save all user data
        $blSuccess = $oUser->save();

        //Add user to two default OXID groups
        $oUser->addToGroup('oxidnewcustomer');
        $oUser->addToGroup('oxidnotyetordered');

        return $blSuccess;
    }

    /**
     * Delete OXID user by ID
     *
     * @param  string $sId The id of the user
     *
     * @return object
     * @throws oxConnectionException
     */
    public function deleteUser($sId)
    {
        $this->getLogger()->debug(
            'Delete oxuser',
            array('oxId' => $sId)
        );

        $sSql = "DELETE FROM oxuser
            WHERE OXID = {$this->getDatabase()->quote($sId)}
              AND OXSHOPID = {$this->getDatabase()->quote($this->getConfig()->getShopId())}";

        return $this->getDatabase()->execute($sSql);
    }

    /**
     * Cleans Amazon pay as the selected one, including all related variables, records and values
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    public function cleanAmazonPay()
    {
        $this->getLogger()->debug(
            'Clean amazon pay'
        );

        $this->getUtilsServer()->setOxCookie('amazon_Login_state_cache', '', time() - 3600, '/');
        $this->getSession()->deleteVariable('amazonLoginToken');
        $this->getModule()->cleanAmazonPay();
    }

    /**
     * Method returns language for Amazon GUI elements
     *
     * @return string
     */
    public function getAmazonLanguage()
    {
        //Get all languages from module settings
        $aLanguages = $this->getConfig()->getConfigParam('aAmazonLanguages');
        $sLanguageAbbr = $this->getLanguage()->getLanguageAbbr();

        //Return Amazon Lang string if it exists in array else return null
        return isset($aLanguages[$sLanguageAbbr]) ? $aLanguages[$sLanguageAbbr] : $sLanguageAbbr;
    }

    /**
     * Returns Language Id by Amazon Language string
     *
     * @param string $sAmazonLanguageString Amazon Language string
     *
     * @return int|bool
     */
    public function getLangIdByAmazonLanguage($sAmazonLanguageString)
    {
        //Get all languages from module settings
        $aLanguages = $this->getConfig()->getConfigParam('aAmazonLanguages');
        $sAbbreviation = array_search($sAmazonLanguageString, $aLanguages);
        $aAllLangIds = $this->getLanguage()->getAllShopLanguageIds();
        return array_search($sAbbreviation, $aAllLangIds);
    }

    /**
     * Returns Language ID to use for made order
     *
     * @param oxOrder $oOrder Order object
     *
     * @return int
     * @throws Exception
     */
    public function getOrderLanguageId(oxOrder $oOrder)
    {
        //Send GetOrderReferenceDetails request to Amazon to get OrderLanguage string
        $oData = $this->getClient()->getOrderReferenceDetails($oOrder, array(), true);

        //If request did not return us the language string return the existing Order lang ID
        if (isset($oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderLanguage) === false) {
            return (int)$oOrder->getFieldData('oxlang');
        }

        //If we have a language string match it to the one in the language mapping array
        $sAmazonLanguageString = (string)$oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderLanguage;
        //Get OXID Language Id by Amazon Language string
        $iLangId = $this->getLangIdByAmazonLanguage($sAmazonLanguageString);

        return ($iLangId !== false) ? (int)$iLangId : (int)$oOrder->getFieldData('oxlang');
    }
}
