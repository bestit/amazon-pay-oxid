<?php

$sVendorAutoloader = realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';

if (file_exists($sVendorAutoloader) === true) {
    include_once realpath(dirname(__FILE__).'/../../').'/vendor/autoload.php';
}

use AmazonPay\Client;
use AmazonPay\ResponseParser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Client model for Amazon Pay transactions
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidClient extends bestitAmazonPay4OxidContainer
{
    const BASIC_FLOW = 'BASIC_FLOW';
    const OPTIMIZED_FLOW = 'OPTIMIZED_FLOW';

    /**
     * Log directory
     */
    const LOG_DIR = 'log/bestitamazon/';

    /**
     * Amazon Widget URL
     *
     * @var string
     */
    protected $_sAmazonWidgetUrlDE = 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/js/Widgets.js';
    protected $_sAmazonWidgetUrlDESandbox = 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/sandbox/js/Widgets.js';
    protected $_sAmazonWidgetUrlUK = 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/js/Widgets.js';
    protected $_sAmazonWidgetUrlUKSandbox = 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/sandbox/js/Widgets.js';
    protected $_sAmazonWidgetUrlUS = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js';
    protected $_sAmazonWidgetUrlUSSandbox = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js';

    /**
     * Amazon Login Widget URL
     *
     * @var string
     */
    protected $_sAmazonLoginWidgetUrlDE = 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/lpa/js/Widgets.js';
    protected $_sAmazonLoginWidgetUrlDESandbox = 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/sandbox/lpa/js/Widgets.js';
    protected $_sAmazonLoginWidgetUrlUK = 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/lpa/js/Widgets.js';
    protected $_sAmazonLoginWidgetUrlUKSandbox = 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/sandbox/lpa/js/Widgets.js';
    protected $_sAmazonLoginWidgetUrlUS = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/lpa/js/Widgets.js';
    protected $_sAmazonLoginWidgetUrlUSSandbox = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/lpa/js/Widgets.js';

    /**
     * Amazon Button URL
     *
     * @var string
     */
    protected $_sAmazonButtonUrlDE = 'https://payments.amazon.de/gp/widgets/button';
    protected $_sAmazonButtonUrlDESandbox = 'https://payments-sandbox.amazon.de/gp/widgets/button';
    protected $_sAmazonButtonUrlUK = 'https://payments.amazon.co.uk/gp/widgets/button';
    protected $_sAmazonButtonUrlUKSandbox = 'https://payments-sandbox.amazon.co.uk/gp/widgets/button';
    protected $_sAmazonButtonUrlUS = 'https://payments.amazon.com/gp/widgets/button';
    protected $_sAmazonButtonUrlUSSandbox = 'https://payments-sandbox.amazon.com/gp/widgets/button';

    /**
     * Amazon Change Payment Link
     *
     * @var string
     */
    protected $_sAmazonPayChangeLinkDE = 'https://payments.amazon.de/jr/your-account/orders?language=';
    protected $_sAmazonPayChangeLinkUK = 'https://payments.amazon.co.uk/jr/your-account/orders?language=';
    protected $_sAmazonPayChangeLinkUS = 'https://payments.amazon.com/jr/your-account/orders?language=';

    /**
     * @var bestitAmazonPay4OxidClient
     */
    private static $_instance = null;

    /**
     * @var null|Logger
     */
    protected $_oLogger = null;
    
    /**
     * @var null|Client
     */
    protected $_oAmazonClient = null;

    /**
     * Singleton instance
     *
     * @return bestitAmazonPay4OxidClient
     * @throws Exception
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = oxNew(__CLASS__);
        }

        return self::$_instance;
    }

    /**
     * Returns the amazon api client.
     *
     * @param array $aConfig
     *
     * @return Client
     * @throws Exception
     */
    protected function _getAmazonClient($aConfig = array())
    {
        if ($this->_oAmazonClient === null) {
            $aConfig = array_merge(
                $aConfig,
                array(
                    'merchant_id' => $this->getConfig()->getConfigParam('sAmazonSellerId'),
                    'access_key' => $this->getConfig()->getConfigParam('sAmazonAWSAccessKeyId'),
                    'secret_key' => $this->getConfig()->getConfigParam('sAmazonSignature'),
                    'client_id' => $this->getConfig()->getConfigParam('sAmazonLoginClientId'),
                    'region' => $this->getConfig()->getConfigParam('sAmazonLocale')
                )
            );

            $this->_oAmazonClient = new Client($aConfig);
            $this->_oAmazonClient->setSandbox((bool)$this->getConfig()->getConfigParam('blAmazonSandboxActive'));
            $this->_oAmazonClient->setLogger($this->getLogger());
        }

        return $this->_oAmazonClient;
    }

    /**
     * @param ResponseParser $response
     *
     * @return stdClass
     */
    protected function _convertResponse(ResponseParser $response)
    {
        return (object) json_decode($response->toJson());
    }

    /**
     * Amazon add sandbox simulation params
     *
     * @param string $sMethod
     * @param array  $aParams
     *
     * @return void
     */
    protected function _addSandboxSimulationParams($sMethod, array &$aParams)
    {
        $this->getLogger()->debug('Try to add sandbox simulation params to request params', array('method' => $sMethod));

        //If Sandbox mode is inactive or Sandbox Simulation is not selected don't add any Simulation Params
        if ((bool) $this->getConfig()->getConfigParam('blAmazonSandboxActive') !== true
            || (bool) $this->getConfig()->getConfigParam('sSandboxSimulation') === false
        ) {
            $this->getLogger()->debug('Simulation or sandbox not active');
            return;
        }

        $sSandboxSimulation = (string) $this->getConfig()->getConfigParam('sSandboxSimulation');

        $aMap = array(
            'setOrderReferenceDetails' => array(
                'SetOrderReferenceDetailsPaymentMethodNotAllowed' => array(
                    'OrderReferenceAttributes.SellerNote' => '{"SandboxSimulation":{"Constraint":"PaymentMethodNotAllowed"}}'
                )
            ),
            'closeOrderReference' => array(
                'CloseOrderReferenceAmazonClosed' => array(
                    'ClosureReason' => '{"SandboxSimulation": {"State":"Closed","ReasonCode":"AmazonClosed"}}'
                )
            ),
            'authorize' => array(
                'AuthorizeInvalidPaymentMethod' => array(
                    'SellerAuthorizationNote' => '{"SandboxSimulation": {"State":"Declined","ReasonCode":"InvalidPaymentMethod","PaymentMethodUpdateTimeInMins":5}}'
                ),
                'AuthorizeAmazonRejected' => array(
                    'SellerAuthorizationNote' => '{"SandboxSimulation": {"State":"Declined","ReasonCode":"AmazonRejected"}}'
                ),
                'AuthorizeTransactionTimedOut' => array(
                    'SellerAuthorizationNote' => '{"SandboxSimulation": {"State":"Declined","ReasonCode":"TransactionTimedOut"}}'
                ),
                'AuthorizeExpiredUnused' => array(
                    'SellerAuthorizationNote' => '{"SandboxSimulation": {"State":"Closed","ReasonCode":"ExpiredUnused","ExpirationTimeInMins":1}}'
                ),
                'AuthorizeAmazonClosed' => array(
                    'SellerAuthorizationNote' => '{"SandboxSimulation": {"State":"Closed","ReasonCode":"AmazonClosed"}}'
                )
            ),
            'capture' => array(
                'CapturePending' => array(
                    'SellerCaptureNote' => '{"SandboxSimulation": {"State":"Pending"}}'
                ),
                'CaptureAmazonRejected' => array(
                    'SellerCaptureNote' => '{"SandboxSimulation": {"State":"Declined","ReasonCode":"AmazonRejected"}}'
                ),
                'CaptureAmazonClosed' => array(
                    'SellerCaptureNote' => '{"SandboxSimulation": {"State":"Closed","ReasonCode":"AmazonClosed"}}'
                )
            ),
            'refund' => array(
                'CaptureAmazonClosed' => array(
                    'SellerRefundNote' => '{"SandboxSimulation": {"State":"Declined","ReasonCode":"AmazonRejected"}}'
                )
            )
        );

        if (isset($aMap[$sMethod][$sSandboxSimulation])) {
            $this->getLogger()->debug(
                'Simulation params attached',
                array('simulation' => $aMap[$sMethod][$sSandboxSimulation])
            );
            $aParams = array_merge($aParams, $aMap[$sMethod][$sSandboxSimulation]);
        }
    }

    /**
     * Returns class property by given property name and other params
     *
     * @param string  $sPropertyName Property name
     * @param boolean $blCommon      Include 'Sandbox' in property name or not
     *
     * @return mixed
     */
    public function getAmazonProperty($sPropertyName, $blCommon = false)
    {
        $sSandboxPrefix = '';

        if ($blCommon === false && (bool)$this->getConfig()->getConfigParam('blAmazonSandboxActive') === true) {
            $sSandboxPrefix = 'Sandbox';
        }

        $sAmazonLocale = $this->getConfig()->getConfigParam('sAmazonLocale');
        $sPropertyName = '_'.$sPropertyName.$sAmazonLocale.$sSandboxPrefix;

        if (property_exists($this, $sPropertyName)) {
            return $this->$sPropertyName;
        }

        return null;
    }

    /**
     * Process order reference.
     *
     * @param oxOrder  $oOrder
     * @param stdClass $oOrderReference
     * @throws Exception
     */
    public function processOrderReference(oxOrder $oOrder, stdClass $oOrderReference)
    {
        $sOrderReferenceStatus = $oOrderReference
            ->OrderReferenceStatus
            ->State;

        $this->getLogger()->debug('Process order reference', array('state' => $sOrderReferenceStatus));

        //Do re-authorization if order was suspended and now it's opened
        if ($sOrderReferenceStatus === 'Open'
            && (string)$oOrder->getFieldData('oxtransstatus') === 'AMZ-Order-Suspended'
        ) {
            $this->getLogger()->debug('Reauthorize order');
            $this->authorize($oOrder);
        } else {
            $this->getLogger()->debug(
                'Set trans status',
                array('status' => $status = 'AMZ-Order-'. $sOrderReferenceStatus)
            );

            $oOrder->assign(array('oxtransstatus' => $status));
            $oOrder->save();
        }
    }

    /**
     * Amazon GetOrderReferenceDetails method
     *
     * @param oxOrder $oOrder
     * @param array   $aParams    Custom parameters to send
     * @param bool    $blReadonly
     *
     * @return stdClass
     * @throws Exception
     */
    public function getOrderReferenceDetails($oOrder = null, array $aParams = array(), $blReadonly = false)
    {
        $aRequestParameters = array();
        $sAmazonOrderReferenceId = ($oOrder === null) ?
            (string)$this->getSession()->getVariable('amazonOrderReferenceId') : '';

        if ($oOrder !== null) {
            $aRequestParameters['amazon_order_reference_id'] = $oOrder->getFieldData('bestitamazonorderreferenceid');
        } elseif ($sAmazonOrderReferenceId !== '') {
            $aRequestParameters['amazon_order_reference_id'] = $sAmazonOrderReferenceId;
            $sLoginToken = (string)$this->getSession()->getVariable('amazonLoginToken');

            if ($sLoginToken !== '') {
                $aRequestParameters['address_consent_token'] = $sLoginToken;
            }
        }

        $this->getLogger()->debug(
            'Fetch order reference object for order reference',
            array(
                'readOnly' => $blReadonly,
                'reference' =>
                    isset($aRequestParameters['amazon_order_reference_id'])
                        ? $aRequestParameters['amazon_order_reference_id']
                        : $sAmazonOrderReferenceId
            )
        );

        //Make request
        $aRequestParameters = array_merge($aRequestParameters, $aParams);

        $oData = $this->_convertResponse($this->_getAmazonClient()->getOrderReferenceDetails($aRequestParameters));

        //Update Order info
        if ($blReadonly === false
            && $oOrder !== null
            && isset($oData->GetOrderReferenceDetailsResult->OrderReferenceDetails->OrderReferenceStatus->State)
        ) {
            $this->processOrderReference(
                $oOrder,
                $oData->GetOrderReferenceDetailsResult->OrderReferenceDetails
            );
        }

        return $oData;
    }

    /**
     * Amazon SetOrderReferenceDetails method
     *
     * @param oxBasket $oBasket            OXID Basket object
     * @param array    $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function setOrderReferenceDetails($oBasket = null, array $aRequestParameters = array())
    {
        //Set default params
        $aRequestParameters['amazon_order_reference_id'] = $this->getSession()->getVariable('amazonOrderReferenceId');

        if ($oBasket !== null) {
            $oActiveShop = $this->getConfig()->getActiveShop();
            $sShopName = $oActiveShop->getFieldData('oxname');
            $sOxidVersion = $oActiveShop->getFieldData('oxversion');
            $sModuleVersion = bestitAmazonPay4Oxid_init::getCurrentVersion();

            $aRequestParameters = array_merge(
                $aRequestParameters,
                array(
                    'amount' => $oBasket->getPrice()->getBruttoPrice(),
                    'currency_code' => $oBasket->getBasketCurrency()->name,
                    'platform_id' => 'A26EQAZK19E0U2',
                    'store_name' => $sShopName,
                    'custom_information' => "created by best it, OXID eShop v{$sOxidVersion}, v{$sModuleVersion}"
                )
            );
        }

        $this->getLogger()->debug('Set order reference details', array('params' => $aRequestParameters));

        $this->_addSandboxSimulationParams('setOrderReferenceDetails', $aRequestParameters);

        return $this->_convertResponse($this->_getAmazonClient()->setOrderReferenceDetails($aRequestParameters));
    }

    /**
     * Amazon ConfirmOrderReference method
     *
     * @param array $aRequestParameters Custom parameters to send
     *
     * @return stdClass response XML
     * @throws Exception
     */
    public function confirmOrderReference(array $aRequestParameters = array())
    {
        //Set params
        $aRequestParameters['amazon_order_reference_id'] = $this->getSession()->getVariable('amazonOrderReferenceId');

        $this->getLogger()->debug('Confirm order reference', array('reference' => $aRequestParameters['amazon_order_reference_id']));

        return $this->_convertResponse($this->_getAmazonClient()->confirmOrderReference($aRequestParameters));
    }

    /**
     * Sets the order status
     *
     * @param oxOrder  $oOrder
     * @param stdClass $oData
     * @param string   $sStatus
     *
     * @return bool
     */
    protected function _setOrderTransactionErrorStatus(oxOrder $oOrder, stdClass $oData, $sStatus = null)
    {
        $this->getLogger()->debug('Try to set transaction order error status', array('sStatus' => $sStatus));

        if (isset($oData->Error->Code) && (bool) $oData->Error->Code !== false) {
            if ($sStatus === null) {
                $sStatus = 'AMZ-Error-'.$oData->Error->Code;
            }

            $this->getLogger()->debug('Persist transaction order error status', array('sStatus' => $sStatus));

            $oOrder->assign(array('oxtransstatus' => $sStatus));
            $oOrder->save();

            return true;
        }

        return false;
    }

    /**
     * @param oxOrder $oOrder
     * @param array   $aRequestParameters
     * @param array   $aFields
     */
    protected function _mapOrderToRequestParameters(oxOrder $oOrder, array &$aRequestParameters, array $aFields)
    {
        $aMap = array(
            'amazon_order_reference_id' => 'bestitamazonorderreferenceid',
            'closure_reason' => 'oxordernr',
            'authorization_amount' => 'oxtotalordersum',
            'currency_code' => 'oxcurrency',
            'authorization_reference_id' => 'bestitamazonorderreferenceid',
            'seller_authorization_note' => 'oxordernr',
            'amazon_authorization_id' => 'bestitamazonauthorizationid',
            'capture_amount' => 'oxtotalordersum',
            'capture_reference_id' => 'bestitamazonorderreferenceid',
            'seller_capture_note' => 'oxordernr',
            'amazon_capture_id' => 'bestitamazoncaptureid',
            'refund_reference_id' => 'bestitamazonorderreferenceid',
            'seller_refund_note' => 'oxordernr',
            'seller_order_id' => 'oxordernr'
        );

        $aPrependMap = array(
            'closure_reason' => 'Authorization%20Close%20Order%20#',
            'seller_authorization_note' => 'Authorization%20Order%20#',
            'seller_refund_note' => 'Refund%20Order%20#',
            'seller_capture_note' => $this->getConfig()->getActiveShop()->getFieldData('oxname').' '
                .$this->getLanguage()->translateString('BESTITAMAZONPAY_ORDER_NO').': '
        );

        $aAppendMap = array(
            'authorization_reference_id' => '_'.$this->getUtilsDate()->getTime(),
            'capture_reference_id' => '_'.$this->getUtilsDate()->getTime(),
            'refund_reference_id' => '_'.$this->getUtilsDate()->getTime()
        );

        foreach ($aFields as $sField) {
            if (isset($aRequestParameters[$sField])) {
                continue;
            }

            if (isset($aMap[$sField])) {
                $aRequestParameters[$sField] = $oOrder->getFieldData($aMap[$sField]);
            }

            if (isset($aPrependMap[$sField])) {
                $aRequestParameters[$sField] = $aPrependMap[$sField].$aRequestParameters[$sField];
            }

            if (isset($aAppendMap[$sField])) {
                $aRequestParameters[$sField] .= $aAppendMap[$sField];
            }
        }
    }

    /**
     * @param string  $sRequestFunction
     * @param oxOrder $oOrder
     * @param array   $aRequestParameters
     * @param array   $aFields
     * @param bool    $blProcessable
     * @param null    $sErrorCode
     *
     * @return stdClass
     * @throws Exception
     */
    protected function _callOrderRequest(
        $sRequestFunction,
        $oOrder,
        array $aRequestParameters,
        array $aFields,
        &$blProcessable = false,
        $sErrorCode = null
    ) {
        $blProcessable = false;

        //Set default params
        if ($oOrder !== null) {
            $this->_mapOrderToRequestParameters($oOrder, $aRequestParameters, $aFields);
        }

        //Make request and return result
        $this->_addSandboxSimulationParams($sRequestFunction, $aRequestParameters);

        $this->getLogger()->debug(
            sprintf('Execute %s amazon api call', $sRequestFunction),
            array('params' => $aRequestParameters)
        );

        $oData = $this->_convertResponse($this->_getAmazonClient()->{$sRequestFunction}($aRequestParameters));

        //Update Order info
        if ($oOrder !== null) {
            $blProcessable = $this->_setOrderTransactionErrorStatus($oOrder, $oData, $sErrorCode) === false;
        }

        return $oData;
    }

    /**
     * Amazon CancelOrderReference method
     *
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function cancelOrderReference($oOrder = null, array $aRequestParameters = array())
    {
        return $this->_callOrderRequest(
            'cancelOrderReference',
            $oOrder,
            $aRequestParameters,
            array('amazon_order_reference_id'),
            $blProcessable,
            'AMZ-Order-Canceled'
        );
    }

    /**
     * Amazon CloseOrderReference method
     *
     * @param oxOrder $oOrder              OXID Order object
     * @param array   $aRequestParameters  Custom parameters to send
     * @param bool    $blUpdateOrderStatus Should the oder status be updated?
     *
     * @throws Exception
     * @return stdClass
     */
    public function closeOrderReference($oOrder = null, $aRequestParameters = array(), $blUpdateOrderStatus = true)
    {
        $oData = $this->_callOrderRequest(
            'closeOrderReference',
            $oOrder,
            $aRequestParameters,
            array('amazon_order_reference_id'),
            $blProcessable,
            'AMZ-Order-Closed'
        );

        //Update Order info
        if ($blUpdateOrderStatus === true && $blProcessable === true) {
            $oOrder->assign(array(
                'oxtransstatus' => 'AMZ-Order-Closed'
            ));

            $this->getLogger()->debug(
                'Update order status after close Reference',
                array('order' => $oOrder->getFieldData('oxordernr'))
            );
            $oOrder->save();
        }

        return $oData;
    }

    /**
     * Amazon CloseAuthorization method
     *
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function closeAuthorization($oOrder = null, $aRequestParameters = array())
    {
        return $this->_callOrderRequest(
            'closeAuthorization',
            $oOrder,
            $aRequestParameters,
            array('amazon_authorization_id', 'closure_reason'),
            $blProcessable,
            'AMZ-Authorize-Closed'
        );
    }

    /**
     * Amazon Authorize method
     *
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     * @param bool    $blForceSync        If true we force the sync mode
     *
     * @return stdClass
     * @throws Exception
     */
    public function authorize($oOrder = null, $aRequestParameters = array(), $blForceSync = false)
    {
        $sMode = $this->getConfig()->getConfigParam('sAmazonMode');
        $aRequestParameters['transaction_timeout'] =
            ($sMode === bestitAmazonPay4OxidClient::BASIC_FLOW || $blForceSync) ? 0 : 1440;

        $this->getLogger()->debug(
            'Authorize order',
            array(
                'timeout' => $aRequestParameters['transaction_timeout'],
                'mode' => $sMode
            )
        );

        $oData = $this->_callOrderRequest(
            'authorize',
            $oOrder,
            $aRequestParameters,
            array(
                'amazon_order_reference_id',
                'authorization_amount',
                'currency_code',
                'authorization_reference_id',
                'seller_authorization_note'
            ),
            $blProcessable
        );

        //Update Order info
        if ($blProcessable === true
            && isset($oData->AuthorizeResult->AuthorizationDetails->AuthorizationStatus->State)
        ) {
            $oDetails = $oData->AuthorizeResult->AuthorizationDetails;
            $oOrder->assign(array(
                'bestitamazonauthorizationid' => $oDetails->AmazonAuthorizationId,
                'oxtransstatus' => 'AMZ-Authorize-'.$oDetails->AuthorizationStatus->State
            ));

            $this->getLogger()->debug(
                'Update order status after authorize',
                array('order' => $oOrder->getFieldData('oxordernr'))
            );

            $oOrder->save();
        }

        return $oData;
    }

    /**
     * Processes the authorization
     *
     * @param oxOrder  $oOrder
     * @param stdClass $oAuthorizationDetails
     * @throws Exception
     */
    public function processAuthorization(oxOrder $oOrder, stdClass $oAuthorizationDetails)
    {
        $oAuthorizationStatus = $oAuthorizationDetails->AuthorizationStatus;

        //Update Order with primary response info
        $oOrder->assign(array('oxtransstatus' => $status = 'AMZ-Authorize-'.$oAuthorizationStatus->State));
        $oOrder->save();

        $this->getLogger()->debug(
            'Update order status after authorize with response info',
            array('status' => $status, 'order' => $oOrder->getFieldData('oxordernr'))
        );

        // Handle Declined response
        if ($oAuthorizationStatus->State === 'Declined'
            && $this->getConfig()->getConfigParam('sAmazonMode') === bestitAmazonPay4OxidClient::OPTIMIZED_FLOW
        ) {
            $this->getLogger()->debug(
                'Handle decline after response for optimized flow',
                array('reason' => $oAuthorizationStatus->ReasonCode)
            );

            switch ($oAuthorizationStatus->ReasonCode) {
                case "InvalidPaymentMethod":
                    /** @var bestitAmazonPay4Oxid_oxEmail $oEmail */
                    $oEmail = $this->getObjectFactory()->createOxidObject('oxEmail');
                    $oEmail->sendAmazonInvalidPaymentEmail($oOrder);
                    break;
                case "AmazonRejected":
                    /** @var bestitAmazonPay4Oxid_oxEmail $oEmail */
                    $oEmail = $this->getObjectFactory()->createOxidObject('oxEmail');
                    $oEmail->sendAmazonRejectedPaymentEmail($oOrder);
                    $this->closeOrderReference($oOrder, array(), false);
                    break;
                default:
                    $this->closeOrderReference($oOrder, array(), false);
            }
        }

        //Authorize handling was selected Direct Capture after Authorize and Authorization status is Open
        if ($oAuthorizationStatus->State === 'Open'
            && $this->getConfig()->getConfigParam('sAmazonCapture') === 'DIRECT'
        ) {
            $this->capture($oOrder);
        }
    }

    /**
     * Amazon GetAuthorizationDetails method
     *
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function getAuthorizationDetails($oOrder = null, array $aRequestParameters = array())
    {
        $oData = $this->_callOrderRequest(
            'getAuthorizationDetails',
            $oOrder,
            $aRequestParameters,
            array('amazon_authorization_id'),
            $blProcessable
        );

        if ($blProcessable === true
            && isset($oData->GetAuthorizationDetailsResult->AuthorizationDetails->AuthorizationStatus->State)
        ) {
            $this->processAuthorization($oOrder, $oData->GetAuthorizationDetailsResult->AuthorizationDetails);
        }

        return $oData;
    }

    /**
     * @param oxOrder  $oOrder
     * @param stdClass $oCaptureDetails
     * @param bool     $blOnlyNotEmpty
     *
     * @return null
     */
    public function setCaptureState(oxOrder $oOrder, stdClass $oCaptureDetails, $blOnlyNotEmpty = false)
    {
        $aFields = array(
            'bestitamazoncaptureid' => $oCaptureDetails->AmazonCaptureId,
            'oxtransstatus' => 'AMZ-Capture-'.$oCaptureDetails->CaptureStatus->State
        );

        $this->getLogger()->debug(
            'Try to set capture state',
            array('state' => $oCaptureDetails->CaptureStatus->State)
        );

        //Update paid date
        if ($oCaptureDetails->CaptureStatus->State === 'Completed'
            && ($blOnlyNotEmpty === false || $oOrder->getFieldData('oxpaid') !== '0000-00-00 00:00:00')
        ) {
            $this->getLogger()->debug('Set paid date into order');
            $aFields['oxpaid'] = date('Y-m-d H:i:s', $this->getUtilsDate()->getTime());
        }

        $oOrder->assign($aFields);
        return $oOrder->save();
    }

    /**
     * Amazon Capture method
     *
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function capture($oOrder = null, $aRequestParameters = array())
    {
        $oData = $this->_callOrderRequest(
            'capture',
            $oOrder,
            $aRequestParameters,
            array(
                'amazon_authorization_id',
                'capture_amount',
                'currency_code',
                'capture_reference_id',
                'seller_capture_note'
            ),
            $blProcessable
        );

        //Update Order info
        if ($blProcessable === true && isset($oData->CaptureResult->CaptureDetails)) {
            $this->setCaptureState($oOrder, $oData->CaptureResult->CaptureDetails);
            $this->closeOrderReference($oOrder, array(), false);
        }

        return $oData;
    }


    /**
     * Amazon GetCaptureDetails method
     *
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function getCaptureDetails($oOrder = null, $aRequestParameters = array())
    {
        $oData = $this->_callOrderRequest(
            'getCaptureDetails',
            $oOrder,
            $aRequestParameters,
            array('amazon_capture_id'),
            $blProcessable
        );

        //Update Order info
        if ($blProcessable === true && isset($oData->GetCaptureDetailsResult->CaptureDetails)) {
            $this->setCaptureState($oOrder, $oData->GetCaptureDetailsResult->CaptureDetails, true);
        }

        return $oData;
    }

    /**
     * Save capture call.
     *
     * @param oxOrder $oOrder
     *
     * @return stdClass|bool
     * @throws Exception
     */
    public function saveCapture($oOrder = null)
    {
        $this->getLogger()->debug('Try to save capture');

        if ((string)$oOrder->getFieldData('bestitAmazonCaptureId') !== '') {
            return $this->getCaptureDetails($oOrder);
        } elseif ((string)$oOrder->getFieldData('bestitAmazonAuthorizationId') !== '') {
            return $this->capture($oOrder);
        }

        return false;
    }

    /**
     * Amazon Refund method
     *
     * @param float   $fPrice             Price to refund
     * @param oxOrder $oOrder             OXID Order object
     * @param array   $aRequestParameters Custom parameters to send
     *
     * @return stdClass
     * @throws Exception
     */
    public function refund($fPrice, $oOrder = null, $aRequestParameters = array())
    {
        //Refund ID
        if ($oOrder !== null) {
            $aRequestParameters['refund_amount'] = $fPrice;
            $this->_mapOrderToRequestParameters(
                $oOrder,
                $aRequestParameters,
                array('amazon_capture_id', 'currency_code', 'refund_reference_id', 'seller_refund_note')
            );
        }

        $this->getLogger()->debug('Refund order', array('price' => $fPrice,'params' => $aRequestParameters));

        //Make request
        $this->_addSandboxSimulationParams('refund', $aRequestParameters);
        $oData = $this->_convertResponse($this->_getAmazonClient()->refund($aRequestParameters));

        //Update/Insert Refund info
        if ($oData && $oOrder !== null) {
            $sError = '';
            $sAmazonRefundId = '';

            if (isset($oData->Error)) {
                $sState = 'Error';
                $sError = $oData->Error->Message;
            } else {
                $sState = $oData->RefundResult->RefundDetails->RefundStatus->State;
                $sAmazonRefundId = $oData->RefundResult->RefundDetails->AmazonRefundId;
            }

            $sId = $oOrder->getFieldData('bestitamazonorderreferenceid').'_'.$this->getUtilsDate()->getTime();

            $sQuery = "
                INSERT bestitamazonrefunds SET 
                  ID = {$this->getDatabase()->quote($sId)},
                  OXORDERID = {$this->getDatabase()->quote($oOrder->getId())},
                  BESTITAMAZONREFUNDID = {$this->getDatabase()->quote($sAmazonRefundId)},
                  AMOUNT = {$fPrice},
                  STATE = {$this->getDatabase()->quote($sState)},
                  ERROR = {$this->getDatabase()->quote($sError)},
                  TIMESTAMP = NOW()";

            $this->getLogger()->debug('Persist refundData', array('query' => $sQuery));

            $this->getDatabase()->execute($sQuery);
        }

        return $oData;
    }

    /**
     * Updates the refund status.
     *
     * @param string $sAmazonRefundId The amazon refund id
     * @param string $sState          The state
     * @param string $sError          The error
     *
     * @throws oxConnectionException
     */
    public function updateRefund($sAmazonRefundId, $sState, $sError = '')
    {
        $this->getLogger()->debug(
            'Update refundData',
            array(
                'id' => $sAmazonRefundId,
                'state' => $sState,
                'error' => $sError
            )
        );

        $sQuery = "
                UPDATE bestitamazonrefunds SET
                  `STATE` = {$this->getDatabase()->quote($sState)},
                  `ERROR` = {$this->getDatabase()->quote($sError)},
                  `TIMESTAMP` = NOW()
                WHERE `BESTITAMAZONREFUNDID` = {$this->getDatabase()->quote($sAmazonRefundId)}";

        $this->getDatabase()->execute($sQuery);
    }

    /**
     * Amazon GetRefundDetails method
     *
     * @param  string $sAmazonRefundId The amazon refund id
     *
     * @return stdClass
     * @throws Exception
     */
    public function getRefundDetails($sAmazonRefundId)
    {
        //Set default params
        $aRequestParameters['amazon_refund_id'] = $sAmazonRefundId;

        //Make request
        $oData = $this->_convertResponse($this->_getAmazonClient()->getRefundDetails($aRequestParameters));

        $this->getLogger()->debug(
            'Fetch refundDetails',
            array(
                'id' => $sAmazonRefundId,
            )
        );

        //Update/Insert Refund info
        if ((array)$oData !== array()) {
            $sError = '';

            if (isset($oData->Error) === true) {
                $sState = 'Error';
                $sError = $oData->Error->Message;
            } else {
                $sState = $oData->GetRefundDetailsResult->RefundDetails->RefundStatus->State;
            }

            $this->updateRefund($sAmazonRefundId, $sState, $sError);
        }

        return $oData;
    }

    /**
     * Sets the order attributes.
     *
     * @param oxOrder $oOrder
     * @param array   $aRequestParameters
     *
     * @return ResponseParser
     * @throws Exception
     */
    public function setOrderAttributes(oxOrder $oOrder, array $aRequestParameters = array())
    {
        $this->_mapOrderToRequestParameters(
            $oOrder,
            $aRequestParameters,
            array('amazon_order_reference_id', 'seller_order_id')
        );

        $this->getLogger()->debug(
            'Set order attributes',
            array(
                'params' => $aRequestParameters,
            )
        );

        return $this->_getAmazonClient()->setOrderAttributes($aRequestParameters);
    }

    /**
     * Returns the user information.
     *
     * @param string $sAccessToken
     *
     * @return mixed
     * @throws Exception
     */
    public function processAmazonLogin($sAccessToken)
    {
        $this->getLogger()->debug('Fetch amazon user info');

        return $this->_getAmazonClient()->getUserInfo($sAccessToken);
    }
}
