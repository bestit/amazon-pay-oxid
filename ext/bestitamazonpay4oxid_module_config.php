<?php

/**
 * Extension for OXID module_config controller
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_module_config extends bestitAmazonPay4Oxid_module_config_parent
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
     * Wrapper function for unit testing.
     */
    protected function _parentSaveConfVars()
    {
        parent::saveConfVars();
    }

    /**
     * Extends the save config variable function to store the amazon config vars
     * from the provided config json object or generate a new cron secret key
     * @throws oxSystemComponentException
     */
    public function saveConfVars()
    {
        $sModuleId = $this->getEditObjectId();

        if ($sModuleId === 'bestitamazonpay4oxid') {
            $sQuickConfig = $this->_getContainer()->getConfig()->getRequestParameter('bestitAmazonPay4OxidQuickConfig');

            try {
                $aQuickConfig = json_decode($sQuickConfig, true);

                $aMap = array(
                    'merchant_id' => array('confstrs', 'sAmazonSellerId'),
                    'access_key' => array('confstrs', 'sAmazonAWSAccessKeyId'),
                    'secret_key' => array('confpassword', 'sAmazonSignature'),
                    'client_id' => array('confstrs', 'sAmazonLoginClientId')
                );

                foreach ($aMap as $sAmazonKey => $aConfigKeys) {
                    if (isset($aQuickConfig[$sAmazonKey]) === true) {
                        list($sMainKey, $sSubKey) = $aConfigKeys;
                        $_POST[$sMainKey][$sSubKey] = $aQuickConfig[$sAmazonKey];
                    }
                }

                if ((bool) $_POST['confbools']['blGenerateNewAmazonCronSecretKey'] === true) {
                    $_POST['confbools']['blGenerateNewAmazonCronSecretKey'] = false;
                    $_POST['confstrs']['sAmazonCronSecretKey'] = self::_generatePassword();
                }
            } catch (\Exception $oException) {
                //Do nothing
            }
        }

        $this->_parentSaveConfVars();
    }

    /**
     * @see https://gist.github.com/tylerhall/521810
     * Generates a strong password of N length containing at least one lower case letter,
     * one uppercase letter and one digit. The remaining characters
     * in the password are chosen at random from those four sets.
     *
     * The available characters in each set are user friendly - there are no ambiguous
     * characters such as i, l, 1, o, 0, etc. This makes it much easier for users to manually
     * type or speak their passwords.
     *
     * @param int $length
     *
     * @return string
     */
    protected static function _generatePassword($length = 15)
    {
        $sets = array();
        $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        $sets[] = '23456789';

        $pool = '';
        $password = '';

        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $pool .= $set;
        }

        $pool = str_split($pool);
        for ($i = 0; $i < $length - count($sets); ++$i) {
            $password .= $pool[array_rand($pool)];
        }
        $password = str_shuffle($password);

        return $password;
    }
}
