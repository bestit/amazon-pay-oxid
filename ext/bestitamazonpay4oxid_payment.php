<?php

use Psr\Log\LoggerInterface;

/**
 * Extension for OXID payment controller
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4Oxid_payment extends bestitAmazonPay4Oxid_payment_parent
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
     * bestitAmazonPay4Oxid_payment constructor.
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
     * Set the amazon reference id
     *
     * @param string $sObjectId               The id of the object
     * @param string $sAmazonOrderReferenceId The amazon reference id
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     */
    protected function _setObjectAmazonReferenceId($sObjectId, $sAmazonOrderReferenceId)
    {
        $sInsert = "INSERT INTO `bestitamazonobject2reference` 
            (`OXOBJECTID`, `AMAZONORDERREFERENCEID`) VALUES ('$sObjectId', '$sAmazonOrderReferenceId')
            ON DUPLICATE KEY UPDATE OXOBJECTID = OXOBJECTID";

        $this->_getContainer()->getDatabase()->execute($sInsert);
    }

    /**
     * Get the amazon reference id
     *
     * @param string $sObjectId The id of the object
     *
     * @throws oxConnectionException
     * @throws oxSystemComponentException
     *
     * @return false|string
     */
    protected function _getObjectAmazonReferenceId($sObjectId)
    {
        $sSelect = "SELECT `AMAZONORDERREFERENCEID` 
            FROM `bestitamazonobject2reference`
            WHERE `OXOBJECTID` = '$sObjectId'";

        return $this->_getContainer()->getDatabase()->getOne($sSelect);
    }

    /**
     * Creates user if user is not logged in, if user is logged in creates new shipping address
     *
     * @param object $oAmazonData User data received from Amazon WS
     *
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _managePrimaryUserData($oAmazonData)
    {
        $oUser = $this->_getContainer()->getActiveUser();
        $oSession = $this->_getContainer()->getSession();

        $this->_oLogger->debug(
            'Manage primary user data'
        );

        //Parse data from Amazon for OXID
        $aParsedData = $this->_getContainer()->getAddressUtil()->parseAmazonAddress($oAmazonData);
        $aDataMap = array(
            'oxfname' => $aParsedData['FirstName'],
            'oxlname' => $aParsedData['LastName'],
            'oxcity' => $aParsedData['City'],
            'oxstateid' => $aParsedData['StateOrRegion'],
            'oxcountryid' => $aParsedData['CountryId'],
            'oxzip' => $aParsedData['PostalCode'],
            'oxstreet' => $aParsedData['Street'],
            'oxstreetnr' => $aParsedData['StreetNr'],
            'oxaddinfo' => $aParsedData['AddInfo'],
            'oxcompany' => $aParsedData['CompanyName'],
        );

        //If user is not logged, create new user and login it
        if ($oUser === false) {
            $this->_oLogger->debug(
                'User not logged in, create new user and login',
                array('dataMap' => $aDataMap)
            );
            $sAmazonOrderReferenceId = $oSession->getVariable('amazonOrderReferenceId');

            /** @var oxUser $oUser */
            $oUser = $this->_getContainer()->getObjectFactory()->createOxidObject('oxUser');
            $oUser->assign(array_merge(
                $aDataMap,
                array(
                    'oxregister' => 0,
                    'oxshopid' => $this->_getContainer()->getConfig()->getShopId(),
                    'oxactive' => 1,
                    'oxusername' => $sAmazonOrderReferenceId . '@amazon.com'
                )
            ));

            if ($oUser->save() !== false) {
                $this->_oLogger->debug(
                    'User created'
                );
                $sUserId = $oUser->getId();

                //Set user id to session
                $oSession->setVariable('usr', $sUserId);

                //Add user to two default OXID groups
                $oUser->addToGroup('oxidnewcustomer');
                $oUser->addToGroup('oxidnotyetordered');

                $this->_setObjectAmazonReferenceId($sUserId, $sAmazonOrderReferenceId);
            }
        } else {
            $sUserId = $oUser->getId();
            $sUserAmazonOrderReferenceId = $this->_getObjectAmazonReferenceId($sUserId);
            $sAmazonOrderReferenceId = $oSession->getVariable('amazonOrderReferenceId');

            $this->_oLogger->debug(
                'User logged in, use existing user',
                array('id' => $sUserId)
            );

            //If our logged in user is the one that was created by us before update details from Amazon WS
            //(Can be selected another user from Amazon Address widget)
            if ($sUserAmazonOrderReferenceId === $sAmazonOrderReferenceId) {
                $this->_oLogger->debug(
                    'Logged in user is the same as the one that was created by us, update user',
                    array('dataMap' => $aDataMap)
                );
                $oUser->assign($aDataMap);
                $oUser->save();
            } else {
                $this->_oLogger->debug(
                    'Logged in user is a new amazon login and user has not updated billing, update user',
                    array('dataMap' => $aDataMap)
                );
                //If we have logged in within Amazon Login for the first time, and user have not updated billing address
                if ((string)$oSession->getVariable('amazonLoginToken') !== ''
                    && (string)$oUser->getFieldData('oxstreet') === ''
                ) {
                    $oUser->assign(array_merge(
                        $aDataMap,
                        array(
                            'oxfon' => $aParsedData['Phone'],
                        )
                    ));
                    $oUser->save();
                }

                //If there exists registered user add Amazon address as users shipping address
                /** @var oxAddress $oDelAddress */
                $oDelAddress = $this->_getContainer()->getObjectFactory()->createOxidObject('oxAddress');

                //Maybe we have already shipping address added for this amazon reference ID. If yes then use it.
                /** @var oxAddress[] $aUserAddresses */
                $aUserAddresses = $oUser->getUserAddresses();

                foreach ($aUserAddresses as $oAddress) {
                    $sAddressId = $oAddress->getId();
                    $sAddressAmazonOrderReferenceId = $this->_getObjectAmazonReferenceId($sAddressId);

                    if ($sAddressAmazonOrderReferenceId === $sAmazonOrderReferenceId) {
                        $oDelAddress->load($sAddressId);
                        break;
                    }
                }

                $this->_oLogger->debug(
                    'Add new shipping address to user and preselect the address',
                    array('dataMap' => $aDataMap)
                );

                //Add new shipping address to user and select it
                $oDelAddress->assign(array_merge(
                    $aDataMap,
                    array('oxuserid' => $oUser->getId())
                ));

                $sDeliveryAddressId = $oDelAddress->save();

                //Set another delivery address as shipping address
                $oSession->setVariable('blshowshipaddress', 1);
                $oSession->setVariable('deladrid', $sDeliveryAddressId);
                $this->_setObjectAmazonReferenceId($sDeliveryAddressId, $sAmazonOrderReferenceId);
            }
        }
    }

    /**
     * Get's primary user details and logins user if one is not logged in
     * Add's new address if user is logged in.
     *
     * @throws Exception
     *
     * @return void
     */
    public function setPrimaryAmazonUserData()
    {
        $oUtils = $this->_getContainer()->getUtils();
        $sShopSecureHomeUrl = $this->_getContainer()->getConfig()->getShopSecureHomeUrl();

        $this->_oLogger->debug(
            'Set primary amazon data'
        );

        //Get primary user data from Amazon
        $oData = $this->_getContainer()->getClient()->getOrderReferenceDetails();
        $oOrderReferenceDetail = isset($oData->GetOrderReferenceDetailsResult->OrderReferenceDetails)
            ? $oData->GetOrderReferenceDetailsResult->OrderReferenceDetails : null;

        if ($oOrderReferenceDetail === null
            || isset($oOrderReferenceDetail->Destination->PhysicalDestination) === false
        ) {
            $this->_oLogger->error(
                'Invalid reference fetched, cleanup and redirect'
            );
            $oUtils->redirect($sShopSecureHomeUrl.'cl=user&fnc=cleanAmazonPay', false);
            return;
        }

        //Creating and(or) logging user
        $sStatus = (string)$oOrderReferenceDetail->OrderReferenceStatus->State;

        if ($sStatus === 'Draft') {
            $this->_oLogger->debug(
                'Draft ORO State detected, redirect to payment page'
            );

            //Manage primary user data
            $oAmazonData = $oOrderReferenceDetail->Destination->PhysicalDestination;
            $this->_managePrimaryUserData($oAmazonData);

            //Recalculate basket to get shipping price for created user
            $this->_getContainer()->getSession()->getBasket()->onUpdate();

            //Redirect with registered user or new shipping address to payment page
            $oUtils->redirect($sShopSecureHomeUrl.'cl=payment', false);
            return;
        }

        $this->_oLogger->error(
            'Other state than "DRAFT" detected, cleanup and redirect'
        );

        $oUtils->redirect($sShopSecureHomeUrl.'cl=user&fnc=cleanAmazonPay', false);
    }

    /**
     * Set's order remark to session
     *
     * @return mixed
     * @throws oxSystemComponentException
     */
    public function validatePayment()
    {
        $oSession = $this->_getContainer()->getSession();
        $oConfig = $this->_getContainer()->getConfig();

        //Don't do anything with order remark if we not under Amazon Pay
        if ((string)$oSession->getVariable('amazonOrderReferenceId') === ''
            || (string)$oConfig->getRequestParameter('paymentid') !== 'bestitamazon'
        ) {
            return parent::validatePayment();
        }

        // order remark
        $sOrderRemark = (string)$oConfig->getRequestParameter('order_remark', true);

        if ($sOrderRemark !== '') {
            $oSession->setVariable('ordrem', $sOrderRemark);
        } else {
            $oSession->deleteVariable('ordrem');
        }

        return parent::validatePayment();
    }


    /**
     * Template variable getter. Returns order remark
     *
     * @return string
     * @throws oxSystemComponentException
     */
    public function getOrderRemark()
    {
        // if already connected, we can use the session
        if ($this->_getContainer()->getActiveUser() !== false) {
            $sOrderRemark = $this->_getContainer()->getSession()->getVariable('ordrem');
        } else {
            // not connected so nowhere to save, we're gonna use what we get from post
            $sOrderRemark = $this->_getContainer()->getConfig()->getRequestParameter('order_remark', true);
        }

        if (!empty($sOrderRemark)) {
            return $this->_getContainer()->getConfig()->checkParamSpecialChars($sOrderRemark);
        }

        return false;
    }
}
