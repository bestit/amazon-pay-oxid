<?php

$sVendorAutoloader = realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';

if (file_exists($sVendorAutoloader) === true) {
    require_once(realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php');
}

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Model for IPN handling
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidIpnHandler extends bestitAmazonPay4OxidContainer
{
    /**
     * Log directory
     */
    const LOG_DIR = 'log/bestitamazon/';

    /**
     * @var bestitAmazonPay4OxidIpnHandler
     */
    private static $_instance = null;

    /**
     * @var null|Logger
     */
    protected $_oLogger = null;

    /**
     * Singleton instance
     *
     * @return bestitAmazonPay4OxidIpnHandler
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
     * Returns the logger.
     *
     * @return Logger
     * @throws Exception
     */
    protected function _getLogger()
    {
        if ($this->_oLogger === null) {
            $this->_oLogger = new Logger('AmazonPayment');
            $sLogFile = $this->getConfig()->getConfigParam('sShopDir').self::LOG_DIR.'ipn.log';
            $this->_oLogger->pushHandler(new StreamHandler($sLogFile));
        }

        return $this->_oLogger;
    }

    /**
     * Method logs IPN response to text file
     *
     * @param string $sLevel
     * @param string $sMessage
     * @param array  $oIpnMessage
     * @throws Exception
     */
    public function logIPNResponse($sLevel, $sMessage, $oIpnMessage = null)
    {
        if ((bool)$this->getConfig()->getConfigParam('blAmazonLogging') !== true) {
            return;
        }

        $aContext = ($oIpnMessage !== null) ? array('ipnMessage' => $oIpnMessage) : array();
        $this->_getLogger()->log($sLevel, $sMessage, $aContext);
    }

    /**
     * Parses SNS message and saves as simplified IPN message into array
     *
     * @param string $sBody
     *
     * @return stdClass|bool
     * @throws Exception
     */
    protected function _getMessage($sBody)
    {
        //Load response xml to object
        try {
            // Get the IPN headers and Message body
            $aHeaders = array();

            foreach ($_SERVER as $sKey => $sValue) {
                if (substr($sKey, 0, 5) !== 'HTTP_') {
                    continue;
                }

                $sHeader = str_replace(' ', '-', str_replace('_', ' ', strtolower(substr($sKey, 5))));
                $aHeaders[$sHeader] = $sValue;
            }

            $ipnHandler = $this->getObjectFactory()->createIpnHandler($aHeaders, $sBody);
            $ipnHandler->setLogger($this->_getLogger());
            return json_decode($ipnHandler->toJson());
        } catch (Exception $oException) {
            $this->logIPNResponse(Logger::ERROR, 'Unable to parse ipn message');
        }

        return false;
    }

    /**
     * @param string $sIdName
     * @param string $sId
     *
     * @return bool|oxOrder
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     */
    protected function _loadOrderById($sIdName, $sId)
    {
        $sQuery = "SELECT OXID 
            FROM oxorder 
            WHERE {$sIdName} = {$this->getDatabase()->quote($sId)}";
        $sOrderId = (string)$this->getDatabase()->getOne($sQuery);

        //Update Order info
        /** @var oxOrder $oOrder */
        $oOrder = $this->getObjectFactory()->createOxidObject('oxOrder');

        if ($oOrder->load($sOrderId) === true) {
            return $oOrder;
        }

        return false;
    }

    /**
     * Handles response for NotificationType = OrderReferenceNotification
     *
     * @param stdClass $oData
     *
     * @return boolean
     * @throws Exception
     */
    protected function _orderReferenceUpdate($oData)
    {
        $sId = $oData->OrderReference->AmazonOrderReferenceId;
        $oOrder = $this->_loadOrderById('BESTITAMAZONORDERREFERENCEID', $sId);

        if ($oOrder !== false && isset($oData->OrderReference->OrderReferenceStatus->State)) {
            $this->getClient()->processOrderReference($oOrder, $oData->OrderReference);
            $this->logIPNResponse(Logger::INFO, 'OK', $oData);
            return true;
        }

        $this->logIPNResponse(Logger::ERROR, "Order with Order Reference ID: {$sId} not found", $oData);
        return false;
    }


    /**
     * Handles response for NotificationType = PaymentAuthorize
     *
     * @param stdClass $oData
     *
     * @return boolean
     * @throws Exception
     */
    protected function _paymentAuthorize($oData)
    {
        $sId = $oData->AuthorizationDetails->AmazonAuthorizationId;
        $oOrder = $this->_loadOrderById('BESTITAMAZONAUTHORIZATIONID', $sId);

        if ($oOrder !== false && isset($oData->AuthorizationDetails->AuthorizationStatus->State)) {
            $this->getClient()->processAuthorization($oOrder, $oData->AuthorizationDetails);
            $this->logIPNResponse(Logger::INFO, 'OK', $oData);
            return true;
        }

        $this->logIPNResponse(Logger::ERROR, "Order with Authorization ID: {$sId} not found", $oData);
        return false;
    }


    /**
     * Handles response for NotificationType = PaymentCapture
     *
     * @param stdClass $oData
     *
     * @return boolean
     * @throws Exception
     */
    protected function _paymentCapture($oData)
    {
        $sId = $oData->CaptureDetails->AmazonCaptureId;
        $oOrder = $this->_loadOrderById('BESTITAMAZONCAPTUREID', $sId);

        if ($oOrder !== false && isset($oData->CaptureDetails->CaptureStatus->State)) {
            $this->getClient()->setCaptureState($oOrder, $oData->CaptureDetails, true);
            $this->logIPNResponse(Logger::INFO, 'OK', $oData);
            return true;
        }

        $this->logIPNResponse(Logger::ERROR, "Order with Capture ID: {$sId} not found", $oData);
        return false;
    }

    /**
     * Handles response for NotificationType = PaymentRefund
     *
     * @param stdClass $oData
     *
     * @return boolean
     * @throws oxConnectionException
     * @throws Exception
     */
    protected function _paymentRefund($oData)
    {
        $sAmazonRefundId = $oData->RefundDetails->AmazonRefundId;
        $sSql = "SELECT COUNT(*)
            FROM bestitamazonrefunds 
            WHERE BESTITAMAZONREFUNDID = {$this->getDatabase()->quote($sAmazonRefundId)}
            LIMIT 1";
        $iMatches = (int)$this->getDatabase()->getOne($sSql);

        //Update Refund info
        if ($iMatches > 0 && isset($oData->RefundDetails->RefundStatus->State)) {
            $this->getClient()->updateRefund(
                $oData->RefundDetails->RefundStatus->State,
                $sAmazonRefundId
            );
            $this->logIPNResponse(Logger::INFO, 'OK', $oData);
            return true;
        }

        $this->logIPNResponse(Logger::ERROR, "Refund with Refund ID: {$sAmazonRefundId} not found", $oData);
        return false;
    }

    /**
     * Process actions by NotificationType
     *
     * @param string $sBody
     *
     * @return bool
     * @throws Exception
     * @throws oxConnectionException
     */
    public function processIPNAction($sBody)
    {
        $oMessage = $this->_getMessage($sBody);

        if (isset($oMessage->NotificationData)) {
            $oData = $oMessage->NotificationData;

            switch ($oMessage->NotificationType) {
                case 'OrderReferenceNotification':
                    return $this->_orderReferenceUpdate($oData);
                case 'PaymentAuthorize':
                    return $this->_paymentAuthorize($oData);
                case 'PaymentCapture':
                    return $this->_paymentCapture($oData);
                case 'PaymentRefund':
                    return $this->_paymentRefund($oData);
                default:
                    $this->logIPNResponse(Logger::ERROR, 'NotificationType in response not found', $oMessage);
                    return false;
            }
        }

        $this->logIPNResponse(Logger::ERROR, 'Invalid ipn message');
        return false;
    }
}