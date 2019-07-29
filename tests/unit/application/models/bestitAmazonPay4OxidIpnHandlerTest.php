<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

use Monolog\Logger;
use Psr\Log\NullLogger;

/**
 * Unit test for class bestitAmazonPay4OxidIpnHandler
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4OxidIpnHandler
 */
class bestitAmazonPay4OxidIpnHandlerTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidClient        $oClient
     * @param oxConfig                          $oConfig
     * @param DatabaseInterface                 $oDatabase
     * @param bestitAmazonPay4OxidObjectFactory $oObjectFactory
     * @param Logger                            $oLogger
     *
     * @return bestitAmazonPay4OxidIpnHandler
     * @throws ReflectionException
     */
    private function _getObject(
        bestitAmazonPay4OxidClient $oClient,
        oxConfig $oConfig,
        DatabaseInterface $oDatabase,
        bestitAmazonPay4OxidObjectFactory $oObjectFactory,
        Logger $oLogger
    ) {
        $oBestitAmazonPay4OxidIpnHandler = new bestitAmazonPay4OxidIpnHandler();
        self::setValue($oBestitAmazonPay4OxidIpnHandler, '_oClientObject', $oClient);
        self::setValue($oBestitAmazonPay4OxidIpnHandler, '_oIpnLogger', $oLogger);
        self::setValue($oBestitAmazonPay4OxidIpnHandler, '_oConfigObject', $oConfig);
        self::setValue($oBestitAmazonPay4OxidIpnHandler, '_oDatabaseObject', $oDatabase);
        self::setValue($oBestitAmazonPay4OxidIpnHandler, '_oObjectFactory', $oObjectFactory);

        return $oBestitAmazonPay4OxidIpnHandler;
    }

    /**
     * @group unit
     * @throws oxSystemComponentException
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidIpnHandler = new bestitAmazonPay4OxidIpnHandler();
        self::assertInstanceOf('bestitAmazonPay4OxidIpnHandler', $oBestitAmazonPay4OxidIpnHandler);
        self::assertInstanceOf('bestitAmazonPay4OxidIpnHandler', bestitAmazonPay4OxidIpnHandler::getInstance());
    }

    /**
     * @group unit
     * @covers ::logIPNResponse
     * @throws ReflectionException
     * @throws Exception
     */
    public function testLogIPNResponse()
    {
        $oConfig = $this->_getConfigMock();

        $oLogger = $this->_getLoggerMock();
        $oLogger->expects($this->exactly(3))
            ->method('log')
            ->withConsecutive(
                array(Logger::WARNING, 'messageA', array('ipnMessage' => array('a' => 'aV'))),
                array(Logger::INFO, 'messageB', array('ipnMessage' => array('b' => 'bV'))),
                array(Logger::ERROR, 'messageC', array('ipnMessage' => array('c' => 'cV')))
            );

        $oBestitAmazon4OxidIpnHandler = $this->_getObject(
            $this->_getClientMock(),
            $oConfig,
            $this->_getDatabaseMock(),
            $this->_getObjectFactoryMock(),
            $oLogger
        );

        $oBestitAmazon4OxidIpnHandler->logIPNResponse(Logger::WARNING, 'messageA', array('a' => 'aV'));
        $oBestitAmazon4OxidIpnHandler->logIPNResponse(Logger::INFO, 'messageB', array('b' => 'bV'));
        $oBestitAmazon4OxidIpnHandler->logIPNResponse(Logger::ERROR, 'messageC', array('c' => 'cV'));
    }

    /**
     * @group unit
     * @covers ::processIPNAction
     * @covers ::_getMessage
     * @covers ::_getLogger
     * @covers ::_loadOrderById
     * @covers ::_orderReferenceUpdate
     * @covers ::_paymentAuthorize
     * @covers ::_paymentCapture
     * @covers ::_paymentRefund
     * @throws Exception
     * @throws ReflectionException
     * @throws oxConnectionException
     */
    public function testProcessIPNAction()
    {
        $aSomeResponse = array('NotificationType' => 'some', 'NotificationData' => 'some');
        $aOrderReferenceNotification = array(
            'NotificationType' => 'OrderReferenceNotification',
            'NotificationData' => array(
                'OrderReference' => array(
                    'AmazonOrderReferenceId' => 'referenceId',
                    'OrderReferenceStatus' => array(
                        'State' => 'StateValue'
                    )
                )
            )
        );
        $aAuthorizationDetailsNotification = array(
            'NotificationType' => 'PaymentAuthorize',
            'NotificationData' => array(
                'AuthorizationDetails' => array(
                    'AmazonAuthorizationId' => 'authorizationId',
                    'AuthorizationStatus' => array(
                        'State' => 'StateValue'
                    )
                )
            )
        );
        $aCaptureDetailsNotification = array(
            'NotificationType' => 'PaymentCapture',
            'NotificationData' => array(
                'CaptureDetails' => array(
                    'AmazonCaptureId' => 'captureId',
                    'CaptureStatus' => array(
                        'State' => 'StateValue'
                    )
                )
            )
        );
        $aRefundDetailsNotification = array(
            'NotificationType' => 'PaymentRefund',
            'NotificationData' => array(
                'RefundDetails' => array(
                    'AmazonRefundId' => 'refundId',
                    'RefundStatus' => array(
                        'State' => 'StateValue'
                    )
                )
            )
        );

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->any())
            ->method('getConfigParam')
            ->with('blAmazonLogging')
            ->will($this->returnValue(true));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(8))
            ->method('quote')
            ->will($this->returnCallback(function ($sValue) {
                return "'{$sValue}'";
            }));

        $oDatabase->expects($this->exactly(8))
            ->method('getOne')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID FROM oxorder WHERE BESTITAMAZONORDERREFERENCEID = 'referenceId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID FROM oxorder WHERE BESTITAMAZONORDERREFERENCEID = 'referenceId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID FROM oxorder WHERE BESTITAMAZONAUTHORIZATIONID = 'authorizationId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID FROM oxorder WHERE BESTITAMAZONAUTHORIZATIONID = 'authorizationId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID FROM oxorder WHERE BESTITAMAZONCAPTUREID = 'captureId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID FROM oxorder WHERE BESTITAMAZONCAPTUREID = 'captureId'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT COUNT(*) FROM bestitamazonrefunds WHERE BESTITAMAZONREFUNDID = 'refundId' LIMIT 1"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT COUNT(*) FROM bestitamazonrefunds WHERE BESTITAMAZONREFUNDID = 'refundId' LIMIT 1"
                ))
            )
            ->will($this->onConsecutiveCalls(
                false,
                'orderId',
                false,
                'orderId',
                false,
                'orderId',
                false,
                1
            ));

        $oIpnHandler = $this->_getAmazonIpnHandlerMock();
        $oIpnHandler->expects($this->exactly(11))
            ->method('toJson')
            ->will($this->onConsecutiveCalls(
                false,
                new stdClass(),
                json_encode($aSomeResponse),
                json_encode($aOrderReferenceNotification),
                json_encode($aOrderReferenceNotification),
                json_encode($aAuthorizationDetailsNotification),
                json_encode($aAuthorizationDetailsNotification),
                json_encode($aCaptureDetailsNotification),
                json_encode($aCaptureDetailsNotification),
                json_encode($aRefundDetailsNotification),
                json_encode($aRefundDetailsNotification)
            ));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(11))
            ->method('createIpnHandler')
            ->with(self::isType('array'), 'bodyContent')
            ->will($this->returnValue($oIpnHandler));

        $oOrder = $this->_getOrderMock();

        $oOrder->expects($this->exactly(6))
            ->method('load')
            ->will($this->returnCallback(function ($sOrderId) {
                return ($sOrderId === 'orderId');
            }));

        $oObjectFactory->expects($this->exactly(6))
            ->method('createOxidObject')
            ->with('oxOrder')
            ->will($this->returnValue($oOrder));

        $oLogger = $this->_getLoggerMock();
        $oIpnHandler->setLogger($oLogger);
        $oLogger->expects($this->exactly(12))
            ->method('log')
            ->withConsecutive(
                array(Logger::ERROR, 'Invalid ipn message', array()),
                array(Logger::ERROR, 'Unable to parse ipn message', array()),
                array(Logger::ERROR, 'Invalid ipn message', array()),
                array(
                    Logger::ERROR,
                    'NotificationType in response not found',
                    array('ipnMessage' => $this->_getResponseObject($aSomeResponse))
                ),
                array(
                    Logger::ERROR,
                    'Order with Order Reference ID: referenceId not found',
                    array('ipnMessage' => $this->_getResponseObject($aOrderReferenceNotification['NotificationData']))
                ),
                array(
                    Logger::INFO,
                    'OK',
                    array('ipnMessage' => $this->_getResponseObject($aOrderReferenceNotification['NotificationData']))
                ),
                array(
                    Logger::ERROR,
                    'Order with Authorization ID: authorizationId not found',
                    array('ipnMessage' => $this->_getResponseObject($aAuthorizationDetailsNotification['NotificationData']))
                ),
                array(
                    Logger::INFO,
                    'OK',
                    array('ipnMessage' => $this->_getResponseObject($aAuthorizationDetailsNotification['NotificationData']))
                ),
                array(
                    Logger::ERROR,
                    'Order with Capture ID: captureId not found',
                    array('ipnMessage' => $this->_getResponseObject($aCaptureDetailsNotification['NotificationData']))
                ),
                array(
                    Logger::INFO,
                    'OK',
                    array('ipnMessage' => $this->_getResponseObject($aCaptureDetailsNotification['NotificationData']))
                ),
                array(
                    Logger::ERROR,
                    'Refund with Refund ID: refundId not found',
                    array('ipnMessage' => $this->_getResponseObject($aRefundDetailsNotification['NotificationData']))
                ),
                array(
                    Logger::INFO,
                    'OK',
                    array('ipnMessage' => $this->_getResponseObject($aRefundDetailsNotification['NotificationData']))
                )
            );

        $oClient = $this->_getClientMock();
        $oClient->expects($this->once())
            ->method('processOrderReference')
            ->with(
                $oOrder,
                $this->_getResponseObject($aOrderReferenceNotification['NotificationData']['OrderReference'])
            );

        $oClient->expects($this->once())
            ->method('processAuthorization')
            ->with(
                $oOrder,
                $this->_getResponseObject($aAuthorizationDetailsNotification['NotificationData']['AuthorizationDetails'])
            );

        $oClient->expects($this->once())
            ->method('setCaptureState')
            ->with(
                $oOrder,
                $this->_getResponseObject($aCaptureDetailsNotification['NotificationData']['CaptureDetails']),
                true
            );

        $oClient->expects($this->once())
            ->method('updateRefund')
            ->with('StateValue', 'refundId');

        $oBestitAmazon4OxidIpnHandler = $this->_getObject(
            $oClient,
            $oConfig,
            $oDatabase,
            $oObjectFactory,
            $oLogger
        );

        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));

        //_orderReferenceUpdate
        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
        self::assertTrue($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));

        //_paymentAuthorize
        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
        self::assertTrue($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));

        //_paymentCapture
        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
        self::assertTrue($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));

        //_paymentRefund
        self::assertFalse($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
        self::assertTrue($oBestitAmazon4OxidIpnHandler->processIPNAction('bodyContent'));
    }
}
