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
     * from the provided config json object.
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
            } catch (\Exception $oException) {
                //Do nothing
            }
        }

        $this->_parentSaveConfVars();
    }
}
