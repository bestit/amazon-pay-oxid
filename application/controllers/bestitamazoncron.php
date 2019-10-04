<?php

use Psr\Log\LoggerInterface;

/**
 * Controller for cronjob tasks
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonCron extends oxUBase
{
    /**
     * @var string
     */
    protected $_sThisTemplate = 'bestitamazonpay4oxidcron.tpl';
    
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
     * bestitAmazonCron constructor.
     */
    public function __construct()
    {
        $this->_oLogger = $this->_getContainer()->getLogger();

        parent::__construct();
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
     * Adds the text to the message.
     *
     * @param string $sText The text string
     */
    protected function _addToMessages($sText)
    {
        $this->_oLogger->debug($sText);
        $aViewData = $this->getViewData();
        $aViewData['sMessage'] = isset($aViewData['sMessage']) ? $aViewData['sMessage'].$sText : $sText;
        $this->setViewData($aViewData);
    }

    /**
     * Processes the order states.
     *
     * @param string $sQuery
     * @param string $sClientFunction
     *
     * @return array
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _processOrderStates($sQuery, $sClientFunction)
    {
        $aResponses = array();
        $aResult = $this->_getContainer()->getDatabase()->getAll($sQuery);

        $this->_oLogger->debug(
            'Fetched orders from db',
            array('orderNumbers' => array_column($aResult, 'OXORDERNR'))
        );

        foreach ($aResult as $aRow) {
            $oOrder = $this->_getContainer()->getObjectFactory()->createOxidObject('oxOrder');

            if ($oOrder->load($aRow['OXID'])) {
                $oData = $this->_getContainer()->getClient()->{$sClientFunction}($oOrder);
                $aResponses[$aRow['OXORDERNR']] = $oData;
            } else {
                $this->_oLogger->debug(
                    'No order for id found',
                    array('id' => $aRow['OXID'])
                );
            }
        }

        return $aResponses;
    }

    /**
     * Authorize unauthorized orders or orders with pending status
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _updateAuthorizedOrders()
    {
        $aProcessed = $this->_processOrderStates(
            "SELECT OXID, OXORDERNR FROM oxorder
            WHERE BESTITAMAZONORDERREFERENCEID != ''
              AND BESTITAMAZONAUTHORIZATIONID != ''
              AND OXTRANSSTATUS = 'AMZ-Authorize-Pending'",
            'getAuthorizationDetails'
        );

        $this->_oLogger->info(
            'Fetched orders and processed',
            array('orderNumbers' => array_keys($aProcessed))
        );

        foreach ($aProcessed as $sOrderNumber => $oData) {
            if (isset($oData->GetAuthorizationDetailsResult->AuthorizationDetails->AuthorizationStatus->State)) {
                $sState = $oData->GetAuthorizationDetailsResult
                    ->AuthorizationDetails
                    ->AuthorizationStatus->State;
                $this->_addToMessages("Authorized Order #{$sOrderNumber} - Status updated to: {$sState}<br/>");
            }
        }
    }

    /**
     * Update declined orders state
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _updateDeclinedOrders()
    {
        $aProcessed = $this->_processOrderStates(
            "SELECT OXID, OXORDERNR FROM oxorder
            WHERE BESTITAMAZONORDERREFERENCEID != ''
              AND BESTITAMAZONAUTHORIZATIONID != ''
              AND OXTRANSSTATUS = 'AMZ-Authorize-Declined'",
            'getOrderReferenceDetails'
        );

        $this->_oLogger->info(
            'Fetched orders and processed',
            array('orderNumbers' => array_keys($aProcessed))
        );

        foreach ($aProcessed as $sOrderNumber => $oData) {
            if (isset($oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderReferenceStatus->State)) {
                $sState = $oData->GetOrderReferenceDetailsResult
                    ->OrderReferenceDetails
                    ->OrderReferenceStatus->State;
                $this->_addToMessages("Declined Order #{$sOrderNumber} - Status updated to: {$sState}<br/>");
            }
        }
    }

    /**
     * Update suspended orders
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _updateSuspendedOrders()
    {
        $aProcessed = $this->_processOrderStates(
            "SELECT OXID, OXORDERNR FROM oxorder
            WHERE BESTITAMAZONORDERREFERENCEID != ''
              AND BESTITAMAZONAUTHORIZATIONID != ''
              AND OXTRANSSTATUS = 'AMZ-Order-Suspended'",
            'getOrderReferenceDetails'
        );

        $this->_oLogger->info(
            'Fetched orders and processed',
            array('orderNumbers' => array_keys($aProcessed))
        );

        foreach ($aProcessed as $sOrderNumber => $oData) {
            if (isset($oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderReferenceStatus->State)) {
                $sState = $oData->GetOrderReferenceDetailsResult
                    ->OrderReferenceDetails
                    ->OrderReferenceStatus->State;
                $this->_addToMessages("Suspended Order #{$sOrderNumber} - Status updated to: {$sState}<br/>");
            }
        }
    }

    /**
     * Capture orders with Authorize status=open
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _captureOrders()
    {
        $sSQLAddShippedCase = '';

        //Capture orders if in module settings was set to capture just shipped orders
        if ((string)$this->_getContainer()->getConfig()->getConfigParam('sAmazonCapture') === 'SHIPPED') {
            $sSQLAddShippedCase = ' AND OXSENDDATE > 0';
        }

        $aProcessed = $this->_processOrderStates(
            "SELECT OXID, OXORDERNR
            FROM oxorder
            WHERE BESTITAMAZONAUTHORIZATIONID != ''
              AND OXTRANSSTATUS = 'AMZ-Authorize-Open' {$sSQLAddShippedCase}",
            'capture'
        );

        $this->_oLogger->info(
            'Fetched orders and processed',
            array('orderNumbers' => array_keys($aProcessed))
        );

        foreach ($aProcessed as $sOrderNumber => $oData) {
            if (isset($oData->CaptureResult->CaptureDetails->CaptureStatus->State)) {
                $sState = $oData->CaptureResult->CaptureDetails->CaptureStatus->State;
                $this->_addToMessages("Capture Order #{$sOrderNumber} - Status updated to: {$sState}<br/>");
            }
        }
    }

    /**
     * Check and update refund details for made refunds
     * @throws Exception
     */
    protected function _updateRefundDetails()
    {
        $sQuery = "SELECT BESTITAMAZONREFUNDID
            FROM bestitamazonrefunds
            WHERE STATE = 'Pending'
              AND BESTITAMAZONREFUNDID != ''";

        $aResult = $this->_getContainer()->getDatabase()->getAll($sQuery);

        $this->_oLogger->info(
            'Fetched orders and processed',
            array('orderNumbers' => array_keys($aResult))
        );

        foreach ($aResult as $aRow) {
            $oData = $this->_getContainer()->getClient()->getRefundDetails($aRow['BESTITAMAZONREFUNDID']);

            if (isset($oData->GetRefundDetailsResult->RefundDetails->RefundStatus->State)) {
                $this->_addToMessages(
                    "Refund ID: {$oData->GetRefundDetailsResult->RefundDetails->RefundReferenceId} - "
                    ."Status: {$oData->GetRefundDetailsResult->RefundDetails->RefundStatus->State}<br/>"
                );
            }
        }
    }

    /**
     * Update suspended orders
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _closeOrders()
    {
        $aProcessed = $this->_processOrderStates(
            "SELECT OXID, OXORDERNR FROM oxorder
            WHERE BESTITAMAZONORDERREFERENCEID != ''
              AND BESTITAMAZONAUTHORIZATIONID != ''
              AND OXTRANSSTATUS = 'AMZ-Capture-Completed'",
            'closeOrderReference'
        );

        $this->_oLogger->info(
            'Fetched orders and processed',
            array('orderNumbers' => array_keys($aProcessed))
        );

        foreach ($aProcessed as $sOrderNumber => $oData) {
            if (isset($oData->CloseOrderReferenceResult, $oData->ResponseMetadata->RequestId)) {
                $this->_addToMessages("Order #{$sOrderNumber} - Closed<br/>");
            }
        }
    }

    /**
     * The render function
     * @throws Exception
     * @throws oxSystemComponentException
     *
     * @return void
     */
    public function render()
    {
        //Increase execution time for the script to run without timeouts
        set_time_limit(3600);

        $this->_oLogger->info('Cronjob started');

        //If ERP mode is enabled do nothing, if IPN or CRON authorize unauthorized orders
        if ((bool)$this->_getContainer()->getConfig()->getConfigParam('blAmazonERP') === true) {
            $this->setViewData(array('sError' => 'ERP mode is ON (Module settings)'));
            $this->_oLogger->info('ERP mode is ON (Module settings)');
        } elseif ((string)$this->_getContainer()->getConfig()->getConfigParam('sAmazonAuthorize') !== 'CRON') {
            $this->setViewData(array('sError' => 'Trigger Authorise via Cronjob mode is turned Off (Module settings)'));
            $this->_oLogger->info('Cronjob state: Trigger Authorise via Cronjob mode is turned Off (Module settings)');
        } else {
            $this->_oLogger->info('Update authorized orders');
            //Authorize unauthorized or Authorize-Pending orders
            $this->_updateAuthorizedOrders();

            $this->_oLogger->info('Update declined orders');
            //Check for declined orders
            $this->_updateDeclinedOrders();

            $this->_oLogger->info('Update suspended orders');
            //Check for suspended orders
            $this->_updateSuspendedOrders();

            $this->_oLogger->info('Capture orders');
            //Capture handling
            $this->_captureOrders();

            $this->_oLogger->info('Update refund stats');
            //Check refund stats
            $this->_updateRefundDetails();

            $this->_oLogger->info('Close orders');
            //Check for order which can be closed
            $this->_closeOrders();

            $this->_oLogger->info('Cronjob finished');
            $this->_addToMessages('Done');
        }

        return $this->_sThisTemplate;
    }

    /**
     * Method returns Operation name
     *
     * @return mixed
     * @throws oxSystemComponentException
     */
    protected function _getOperationName()
    {
        $operation = lcfirst($this->_getContainer()->getConfig()->getRequestParameter('operation'));

        if (method_exists($this->_getContainer()->getClient(), $operation)) {
            return $operation;
        }

        $this->setViewData(array('sError' => "Operation '{$operation}' does not exist"));
        return false;
    }

    /**
     * Method returns Order object
     *
     * @return null|oxOrder
     * @throws oxSystemComponentException
     */
    protected function _getOrder()
    {
        $sOrderId = $this->_getContainer()->getConfig()->getRequestParameter('oxid');

        if ($sOrderId !== null) {
            /** @var oxOrder $oOrder */
            $oOrder = $this->_getContainer()->getObjectFactory()->createOxidObject('oxOrder');

            if ($oOrder->load($sOrderId) === true) {
                return $oOrder;
            }
        }

        return null;
    }

    /**
     * Method returns Parameters from GET aParam array
     *
     * @return array
     * @throws oxSystemComponentException
     */
    protected function _getParams()
    {
        $aResult = array();
        $aParams = (array)$this->_getContainer()->getConfig()->getRequestParameter('aParams');

        foreach ($aParams as $sKey => $sValue) {
            $aResult[html_entity_decode($sKey)] = html_entity_decode($sValue);
        }

        return $aResult;
    }

    /**
     * Makes request to Amazon methods
     *
     * amazonCall method Calling examples:
     *
     * index.php?cl=bestitamazoncron&fnc=amazonCall&operation=Authorize&oxid=87feca21ce31c34f0d3dceb8197a2375
     * index.php?cl=bestitamazoncron&fnc=amazonCall&operation=Authorize&aParams[AmazonOrderReferenceId]=51fd6a7381e7a0220b0f166fe331e420&aParams[AmazonAuthorizationId]=S02-8774768-9373076-A060413
     * @throws oxSystemComponentException
     *
     * @return void
     */
    public function amazonCall()
    {
        $sOperation = $this->_getOperationName();

        if ($sOperation !== false) {
            $oResult = $this->_getContainer()->getClient()->{$sOperation}(
                $order = $this->_getOrder(),
                $params = $this->_getParams()
            );

            $this->_oLogger->info(
                'Execute specific amazon call',
                array(
                    'operation' => $sOperation,
                    'oxid' => $order ? $order->getId() : null,
                    'params' => $params
                )
            );

            $this->_addToMessages('<pre>'.print_r($oResult, true).'</pre>');
            return;
        }

        $this->setViewData(array(
            'sError' => 'Please specify operation you want to call (&operation=) '
                .'and use &oxid= parameter to specify order ID or use &aParams[\'key\']=value'
        ));
    }
}
