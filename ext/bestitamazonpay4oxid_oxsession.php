<?php
/**
 * This Software is the property of best it GmbH & Co. KG and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license is
 * a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * bestitamazonpay4oxid_oxsession.php
 *
 * The bestitAmazonPay4Oxid_oxSession class file.
 *
 * PHP versions 5
 *
 * @category  bestitAmazonPay4Oxid
 * @package   bestitAmazonPay4Oxid
 * @author    best it GmbH & Co. KG - Alexander Schneider <schneider@bestit-online.de>
 * @copyright 2017 best it GmbH & Co. KG
 * @version   GIT: $Id$
 * @link      http://www.bestit-online.de
 */

/**
 * Class bestitAmazonPay4Oxid_oxSession
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

