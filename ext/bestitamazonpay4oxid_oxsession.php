<?php

/**
 * Extension for OXID oxSession model
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_oxSession extends bestitAmazonPay4Oxid_oxSession_parent
{
    /**
     * returns configuration array with info which parameters require session
     * start
     *
     * @return array
     */
    protected function _getRequireSessionWithParams()
    {
        $this->_aRequireSessionWithParams['fnc']['amazonLogin'] = true;

        return parent::_getRequireSessionWithParams();
    }
}
