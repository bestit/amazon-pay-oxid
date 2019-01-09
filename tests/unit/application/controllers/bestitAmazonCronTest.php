<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonCron
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonCron
 */
class bestitAmazonCronTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonCron
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonCron = new bestitAmazonCron();
        self::setValue($oBestitAmazonCron, '_oContainer', $oContainer);

        return $oBestitAmazonCron;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonCron = new bestitAmazonCron();
        self::assertInstanceOf('bestitAmazonCron', $oBestitAmazonCron);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonCron = new bestitAmazonCron();
        self::assertInstanceOf('bestitAmazonPay4OxidContainer', self::callMethod($oBestitAmazonCron, '_getContainer'));
    }

    /**
     * @group unit
     * @covers ::render()
     * @covers ::_updateAuthorizedOrders()
     * @covers ::_updateDeclinedOrders()
     * @covers ::_updateSuspendedOrders()
     * @covers ::_captureOrders()
     * @covers ::_updateRefundDetails()
     * @covers ::_processOrderStates()
     * @covers ::_addToMessages()
     * @throws Exception
     * @throws ReflectionException
     * @throws oxSystemComponentException
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(6))
            ->method('getConfigParam')
            ->withConsecutive(
                array('blAmazonERP'),
                array('blAmazonERP'),
                array('sAmazonAuthorize'),
                array('blAmazonERP'),
                array('sAmazonAuthorize'),
                array('sAmazonCapture')
            )
            ->will($this->onConsecutiveCalls(
                true,
                false,
                'some',
                false,
                'CRON',
                'SHIPPED'
            ));

        $oContainer->expects($this->exactly(6))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oOrderResponse = array(
            array('OXID' => 1, 'OXORDERNR' => 123),
            array('OXID' => 2, 'OXORDERNR' => 234),
            array('OXID' => 3, 'OXORDERNR' => 345)
        );

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(6))
            ->method('getAll')
            ->withConsecutive(
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID, OXORDERNR FROM oxorder
                    WHERE BESTITAMAZONORDERREFERENCEID != ''
                      AND BESTITAMAZONAUTHORIZATIONID != ''
                      AND OXTRANSSTATUS = 'AMZ-Authorize-Pending'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID, OXORDERNR FROM oxorder
                    WHERE BESTITAMAZONORDERREFERENCEID != ''
                      AND BESTITAMAZONAUTHORIZATIONID != ''
                      AND OXTRANSSTATUS = 'AMZ-Authorize-Declined'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID, OXORDERNR FROM oxorder
                    WHERE BESTITAMAZONORDERREFERENCEID != ''
                      AND BESTITAMAZONAUTHORIZATIONID != ''
                      AND OXTRANSSTATUS = 'AMZ-Order-Suspended'"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID, OXORDERNR FROM oxorder
                    WHERE BESTITAMAZONAUTHORIZATIONID != ''
                      AND OXTRANSSTATUS = 'AMZ-Authorize-Open'
                      AND OXSENDDATE > 0"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT BESTITAMAZONREFUNDID
                    FROM bestitamazonrefunds
                    WHERE STATE = 'Pending'
                      AND BESTITAMAZONREFUNDID != ''"
                )),
                array(new MatchIgnoreWhitespace(
                    "SELECT OXID, OXORDERNR FROM oxorder
                    WHERE BESTITAMAZONORDERREFERENCEID != ''
                      AND BESTITAMAZONAUTHORIZATIONID != ''
                      AND OXTRANSSTATUS = 'AMZ-Capture-Completed'"
                ))
            )
            ->will($this->onConsecutiveCalls(
                $oOrderResponse,
                $oOrderResponse,
                $oOrderResponse,
                $oOrderResponse,
                array(array('BESTITAMAZONREFUNDID' => 1), array('BESTITAMAZONREFUNDID' => 2)),
                $oOrderResponse
            ));

        $oContainer->expects($this->exactly(6))
            ->method('getDatabase')
            ->will($this->returnValue($oDatabase));

        $oOrder = $this->_getOrderMock();
        $oOrder->expects($this->exactly(15))
            ->method('load')
            ->withConsecutive(
                array(1),
                array(2),
                array(3),
                array(1),
                array(2),
                array(3),
                array(1),
                array(2),
                array(3),
                array(1),
                array(2),
                array(3),
                array(1),
                array(2),
                array(3)
            )
            ->will($this->returnCallback(function ($sId) {
                return ($sId === 1 || $sId === 2);
            }));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(15))
            ->method('createOxidObject')
            ->with('oxOrder')
            ->will($this->returnValue($oOrder));

        $oContainer->expects($this->exactly(15))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(2))
            ->method('getAuthorizationDetails')
            ->with($oOrder)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'GetAuthorizationDetailsResult' => array(
                        'AuthorizationDetails' => array(
                            'AuthorizationStatus' => array(
                                'State' => 'authorizationStateValue'
                            )
                        )
                    )
                )
            )));
        $oClient->expects($this->exactly(4))
            ->method('getOrderReferenceDetails')
            ->with($oOrder)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderReferenceStatus' => array(
                                'State' => 'referenceStatusValue'
                            )
                        )
                    )
                )),
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'GetOrderReferenceDetailsResult' => array(
                        'OrderReferenceDetails' => array(
                            'OrderReferenceStatus' => array(
                                'State' => 'referenceStatusValue'
                            )
                        )
                    )
                ))
            ));
        $oClient->expects($this->exactly(2))
            ->method('capture')
            ->with($oOrder)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'CaptureResult' => array(
                        'CaptureDetails' => array(
                            'CaptureStatus' => array(
                                'State' => 'captureStateValue'
                            )
                        )
                    )
                ))
            ));
        $oClient->expects($this->exactly(2))
            ->method('getRefundDetails')
            ->withConsecutive(array(1), array(2))
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'GetRefundDetailsResult' => array(
                        'RefundDetails' => array(
                            'RefundReferenceId' => 'referenceId',
                            'RefundStatus' => array(
                                'State' => 'refundStateValue'
                            )
                        )
                    )
                ))
            ));
        $oClient->expects($this->exactly(2))
            ->method('closeOrderReference')
            ->with($oOrder)
            ->will($this->onConsecutiveCalls(
                $this->_getResponseObject(),
                $this->_getResponseObject(array(
                    'CloseOrderReferenceResult' => array(),
                    'ResponseMetadata' => array(
                        'RequestId' => '1'
                    )
                ))
            ));

        $oContainer->expects($this->exactly(12))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonIpn = $this->_getObject($oContainer);
        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array('sError' => 'ERP mode is ON (Module settings)'),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array('sError' => 'Trigger Authorise via Cronjob mode is turned Off (Module settings)'),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self:self::setValue($oBestitAmazonIpn, '_aViewData', array());
        self::assertEquals('bestitamazonpay4oxidcron.tpl', $oBestitAmazonIpn->render());
        self::assertAttributeEquals(
            array(
                'sMessage' => 'Authorized Order #234 - Status updated to: authorizationStateValue<br/>'
                    .'Declined Order #234 - Status updated to: referenceStatusValue<br/>'
                    .'Suspended Order #234 - Status updated to: referenceStatusValue<br/>'
                    .'Capture Order #234 - Status updated to: captureStateValue<br/>'
                    .'Refund ID: referenceId - Status: refundStateValue<br/>'
                    .'Order #234 - Closed<br/>'
                    .'Done'
            ),
            '_aViewData',
            $oBestitAmazonIpn
        );
    }

    /**
     * @group unit
     * @covers ::amazonCall()
     * @covers ::_getOperationName()
     * @covers ::_getOrder()
     * @covers ::_getParams()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testAmazonCall()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(10))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('operation'),
                array('operation'),
                array('oxid'),
                array('aParams'),
                array('operation'),
                array('oxid'),
                array('aParams'),
                array('operation'),
                array('oxid'),
                array('aParams')
            )
            ->will($this->onConsecutiveCalls(
                'some',
                'Capture',
                null,
                null,
                'Capture',
                'firstOrderId',
                array('key&lt;' => 'value&lt;'),
                'Capture',
                'secondOrderId',
                array('key&lt;' => 'value&lt;')
            ));

        $oContainer->expects($this->exactly(10))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oOrder = $this->_getOrderMock();
        $oOrder->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                array('firstOrderId'),
                array('secondOrderId')
            )
            ->will($this->onConsecutiveCalls(false, true));

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxOrder')
            ->will($this->returnValue($oOrder));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        $oClient = $this->_getClientMock();
        $oClient->expects($this->exactly(3))
            ->method('capture')
            ->withConsecutive(
                array(null, array()),
                array(null, array('key<' => 'value<')),
                array($oOrder, array('key<' => 'value<'))
            )->will($this->onConsecutiveCalls(
               $this->_getResponseObject(array('firstResponse')),
               $this->_getResponseObject(array('secondResponse')),
               $this->_getResponseObject(array('thirdResponse'))
            ));

        $oContainer->expects($this->exactly(7))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonIpn = $this->_getObject($oContainer);

        self::assertNull($oBestitAmazonIpn->amazonCall());
        self::assertAttributeEquals(
            array(
                'sError' => 'Please specify operation you want to call (&operation=) '
                    .'and use &oxid= parameter to specify order ID or use &aParams[\'key\']=value'
            ),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::setValue($oBestitAmazonIpn, '_aViewData', array());
        self::assertNull($oBestitAmazonIpn->amazonCall());
        self::assertAttributeEquals(
            array('sMessage' => "<pre>stdClass Object\n(\n    [0] => firstResponse\n)\n</pre>"),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::setValue($oBestitAmazonIpn, '_aViewData', array());
        self::assertNull($oBestitAmazonIpn->amazonCall());
        self::assertAttributeEquals(
            array('sMessage' => "<pre>stdClass Object\n(\n    [0] => secondResponse\n)\n</pre>"),
            '_aViewData',
            $oBestitAmazonIpn
        );

        self::setValue($oBestitAmazonIpn, '_aViewData', array());
        self::assertNull($oBestitAmazonIpn->amazonCall());
        self::assertAttributeEquals(
            array('sMessage' => "<pre>stdClass Object\n(\n    [0] => thirdResponse\n)\n</pre>"),
            '_aViewData',
            $oBestitAmazonIpn
        );
    }
}
