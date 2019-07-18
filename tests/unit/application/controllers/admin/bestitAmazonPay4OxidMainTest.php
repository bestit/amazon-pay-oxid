<?php

require_once dirname(__FILE__).'/../../../bestitAmazon4OxidUnitTestCase.php';


/**
 * Unit test for class bestitAmazonPay4Oxid_main
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_main
 */
class bestitAmazonPay4OxidMainTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_main
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidMain = new bestitAmazonPay4Oxid_main();
        self::setValue($oBestitAmazonPay4OxidMain, '_oContainer', $oContainer);

        return $oBestitAmazonPay4OxidMain;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidMain = new bestitAmazonPay4Oxid_main();
        self::assertInstanceOf('bestitAmazonPay4Oxid_main', $oBestitAmazonPay4OxidMain);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidMain = new bestitAmazonPay4Oxid_main();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidMain, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::render()
     * @covers ::_getPaymentType()
     * @throws oxSystemComponentException
     * @throws ReflectionException
     */
    public function testRender()
    {
        $oContainer = $this->_getContainerMock();

        $oCurrency = $this->getMock('oxCurrency');

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(7))
            ->method('getActShopCurrencyObject')
            ->will($this->returnValue($oCurrency));

        $oConfig->expects($this->exactly(7))
            ->method('getConfigParam')
            ->with('aOrderfolder')
            ->will($this->returnValue('orderFolder'));

        $oContainer->expects($this->exactly(7))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oOrder = $this->_getOrderMock();

        $oOrder->expects($this->exactly(5))
            ->method('load')
            ->withConsecutive(
                array(1),
                array(2),
                array(2),
                array(2),
                array(2)
            )
            ->will($this->returnCallback(function ($sId) {
                return ($sId === 2);
            }));

        $oOrder->expects($this->exactly(14))
            ->method('getOrderSum')
            ->will($this->returnCallback(function ($blFull) {
                return ($blFull !== true) ? 10 : 1;
            }));

        $oOrder->expects($this->exactly(14))
            ->method('getOrderCnt')
            ->will($this->returnCallback(function ($blFull) {
                return ($blFull !== true) ? 100 : 50;
            }));

        $oOrder->expects($this->exactly(8))
            ->method('getFieldData')
            ->withConsecutive(
                array('oxpaymenttype'),
                array('oxtsprotectcosts'),
                array('oxpaymenttype'),
                array('oxtsprotectcosts'),
                array('oxpaymenttype'),
                array('oxtsprotectcosts'),
                array('oxpaymenttype'),
                array('oxtsprotectcosts')
            )
            ->will($this->onConsecutiveCalls(
                null,
                null,
                null,
                11,
                1,
                11,
                2,
                11
            ));

        $oOrder->expects($this->exactly(4))
            ->method('getProductVats')
            ->will($this->returnValue(array('productVats')));

        $oOrderArticles = $this->getMock('oxList', array(), array(), '', false);

        $oOrder->expects($this->exactly(4))
            ->method('getOrderArticles')
            ->will($this->returnValue($oOrderArticles));

        $oWrapping = $this->getMock('oxWrapping', array(), array(), '', false);

        $oOrder->expects($this->exactly(4))
            ->method('getGiftCard')
            ->will($this->returnValue($oWrapping));

        $oDeliverySet = $this->getMock('oxDeliverySet', array(), array(), '', false);

        $oOrder->expects($this->exactly(4))
            ->method('getDelSet')
            ->will($this->returnValue($oDeliverySet));

        $oUserPayment = $this->getMock('oxUserPayment', array(), array(), '', false);

        $oOrder->expects($this->exactly(4))
            ->method('getPaymentType')
            ->will($this->onConsecutiveCalls($oUserPayment, false, false, false));

        $oLanguage = $this->_getLanguageMock();

        $oLanguage->expects($this->exactly(17))
            ->method('formatCurrency')
            ->will($this->returnCallback(function ($fValue, $oCurrency) {
                return "{$fValue} EUR";
            }));

        $oContainer->expects($this->exactly(7))
            ->method('getLanguage')
            ->will($this->returnValue($oLanguage));

        $oPayment = $this->getMock('oxPayment', array(), array(), '', false);
        $oPayment->expects($this->exactly(2))
            ->method('load')
            ->withConsecutive(
                array(1),
                array(2)
            )
            ->will($this->returnCallback(function ($sId) {
                return ($sId === 2);
            }));

        $oPayment->expects($this->once())
            ->method('getFieldData')
            ->with('oxdesc')
            ->will($this->returnValue('paymentDescription'));

        $oNewUserPayment = $this->getMock('oxUserPayment', array(), array(), '', false);

        $oNewUserPayment->expects($this->once())
            ->method('assign')
            ->with(
                array('oxdesc' => 'paymentDescription')
            );

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(10))
            ->method('createOxidObject')
            ->withConsecutive(
                array('oxOrder'),
                array('oxOrder'),
                array('oxOrder'),
                array('oxOrder'),
                array('oxOrder'),
                array('oxOrder'),
                array('oxPayment'),
                array('oxOrder'),
                array('oxPayment'),
                array('oxUserPayment')
            )
            ->will($this->onConsecutiveCalls(
                $oOrder,
                $oOrder,
                $oOrder,
                $oOrder,
                $oOrder,
                $oOrder,
                $oPayment,
                $oOrder,
                $oPayment,
                $oNewUserPayment
            ));

        $oContainer->expects($this->exactly(10))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));


        $oBestitAmazonPay4OxidMain = $this->_getObject($oContainer);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', null);
        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', -1);
        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', 1);
        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', 2);
        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);
        self::assertEquals($oOrder, $aViewData['edit']);
        self::assertEquals(array('productVats'), $aViewData['aProductVats']);
        self::assertEquals($oOrderArticles, $aViewData['orderArticles']);
        self::assertEquals($oWrapping, $aViewData['giftCard']);
        self::assertEquals($oUserPayment, $aViewData['paymentType']);
        self::assertEquals($oDeliverySet, $aViewData['deliveryType']);

        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);
        self::assertEquals($oOrder, $aViewData['edit']);
        self::assertEquals(array('productVats'), $aViewData['aProductVats']);
        self::assertEquals($oOrderArticles, $aViewData['orderArticles']);
        self::assertEquals($oWrapping, $aViewData['giftCard']);
        self::assertEquals(false, $aViewData['paymentType']);
        self::assertEquals($oDeliverySet, $aViewData['deliveryType']);
        self::assertEquals('11 EUR', $aViewData['tsprotectcosts']);

        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);
        self::assertEquals($oOrder, $aViewData['edit']);
        self::assertEquals(array('productVats'), $aViewData['aProductVats']);
        self::assertEquals($oOrderArticles, $aViewData['orderArticles']);
        self::assertEquals($oWrapping, $aViewData['giftCard']);
        self::assertEquals(false, $aViewData['paymentType']);
        self::assertEquals($oDeliverySet, $aViewData['deliveryType']);
        self::assertEquals('11 EUR', $aViewData['tsprotectcosts']);

        self::assertEquals('bestitamazonpay4oxid_main.tpl' , $oBestitAmazonPay4OxidMain->render());
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('1 EUR', $aViewData['ordersum']);
        self::assertEquals(50, $aViewData['ordercnt']);
        self::assertEquals('10 EUR', $aViewData['ordertotalsum']);
        self::assertEquals(100, $aViewData['ordertotalcnt']);
        self::assertEquals('orderFolder', $aViewData['afolder']);
        self::assertEquals($oCurrency, $aViewData['currency']);
        self::assertEquals($oOrder, $aViewData['edit']);
        self::assertEquals(array('productVats'), $aViewData['aProductVats']);
        self::assertEquals($oOrderArticles, $aViewData['orderArticles']);
        self::assertEquals($oWrapping, $aViewData['giftCard']);
        self::assertEquals($oNewUserPayment, $aViewData['paymentType']);
        self::assertEquals($oDeliverySet, $aViewData['deliveryType']);
        self::assertEquals('11 EUR', $aViewData['tsprotectcosts']);
    }

    /**
     * @group unit
     * @covers ::refundAmazonOrder()
     * @throws Exception
     */
    public function testRefundAmazonOrder()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(5))
            ->method('getRequestParameter')
            ->withConsecutive(
                array('blAmazonConfirmRefund'),
                array('blAmazonConfirmRefund'),
                array('fAmazonRefundAmount'),
                array('blAmazonConfirmRefund'),
                array('fAmazonRefundAmount')
            )
            ->will($this->onConsecutiveCalls(
                null,
                1,
                '0,0',
                1,
                '1,2'
            ));

        $oContainer->expects($this->exactly(5))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oLanguage = $this->_getLanguageMock();

        $oLanguage->expects($this->exactly(4))
            ->method('translateString')
            ->withConsecutive(
                array('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX'),
                array('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX'),
                array('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX'),
                array('BESTITAMAZONPAY_INVALID_REFUND_AMOUNT')
            )
            ->will($this->returnCallback(function ($sValue) {
                return "{$sValue}|Translated";
            }));

        $oContainer->expects($this->exactly(4))
            ->method('getLanguage')
            ->will($this->returnValue($oLanguage));

        $oOrder = $this->_getOrderMock();

        $oOrder->expects($this->exactly(2))
            ->method('load')
            ->with(1);

        $oObjectFactory = $this->_getObjectFactoryMock();
        $oObjectFactory->expects($this->exactly(2))
            ->method('createOxidObject')
            ->with('oxOrder')
            ->will($this->returnValue($oOrder));

        $oContainer->expects($this->exactly(2))
            ->method('getObjectFactory')
            ->will($this->returnValue($oObjectFactory));

        $oClient = $this->_getClientMock();

        $oClient->expects($this->once())
            ->method('refund')
            ->with( 1.2, $oOrder);

        $oContainer->expects($this->once())
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidMain = $this->_getObject($oContainer);

        $oBestitAmazonPay4OxidMain->refundAmazonOrder();
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX|Translated', $aViewData['bestitrefunderror']);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', -1);
        $oBestitAmazonPay4OxidMain->refundAmazonOrder();
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX|Translated', $aViewData['bestitrefunderror']);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', 1);
        $oBestitAmazonPay4OxidMain->refundAmazonOrder();
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX|Translated', $aViewData['bestitrefunderror']);
        $oBestitAmazonPay4OxidMain->refundAmazonOrder();
        $aViewData = self::readAttribute($oBestitAmazonPay4OxidMain, '_aViewData');
        self::assertEquals('BESTITAMAZONPAY_INVALID_REFUND_AMOUNT|Translated', $aViewData['bestitrefunderror']);
        $oBestitAmazonPay4OxidMain->refundAmazonOrder();
    }

    /**
     * @group unit
     * @covers ::getRefunds()
     * @throws oxSystemComponentException
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testGetRefunds()
    {
        $oContainer = $this->_getContainerMock();

        $oDatabase = $this->_getDatabaseMock();

        $oDatabase->expects($this->once())
            ->method('quote')
            ->with(1)
            ->will($this->returnValue('\'1\''));

        $oDatabase->expects($this->once())
            ->method('getAll')
            ->with(new MatchIgnoreWhitespace(
                "SELECT *
                FROM bestitamazonrefunds
                WHERE OXORDERID = '1'
                ORDER BY TIMESTAMP"
            ))
            ->will($this->returnValue(array('refunds')));

        $oContainer->expects($this->once())
            ->method('getDatabase')
            ->will($this->returnValue($oDatabase));

        $oBestitAmazonPay4OxidMain = $this->_getObject($oContainer);

        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', 1);
        self::assertEquals(array('refunds'), $oBestitAmazonPay4OxidMain->getRefunds());
    }

    /**
     * @group unit
     * @covers ::getRefundsStatus()
     * @throws Exception
     */
    public function testGetRefundsStatus()
    {
        $oContainer = $this->_getContainerMock();

        $oDatabase = $this->_getDatabaseMock();

        $oDatabase->expects($this->once())
            ->method('quote')
            ->with(1)
            ->will($this->returnValue('\'1\''));

        $oDatabase->expects($this->once())
            ->method('getAll')
            ->with(new MatchIgnoreWhitespace(
                "SELECT BESTITAMAZONREFUNDID
                FROM bestitamazonrefunds
                WHERE STATE = 'Pending'
                  AND BESTITAMAZONREFUNDID != ''
                  AND OXORDERID = '1'"
            ))
            ->will($this->returnValue(array(
                array('BESTITAMAZONREFUNDID' => 'refundIdOne'),
                array('BESTITAMAZONREFUNDID' => 'refundIdTwo')
            )));

        $oContainer->expects($this->once())
            ->method('getDatabase')
            ->will($this->returnValue($oDatabase));

        $oClient = $this->_getClientMock();

        $oClient->expects($this->exactly(2))
            ->method('getRefundDetails')
            ->withConsecutive(
                array('refundIdOne'),
                array('refundIdTwo')
            );

        $oContainer->expects($this->exactly(2))
            ->method('getClient')
            ->will($this->returnValue($oClient));

        $oBestitAmazonPay4OxidMain = $this->_getObject($oContainer);

        $oBestitAmazonPay4OxidMain->getRefundsStatus();
        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', -1);
        $oBestitAmazonPay4OxidMain->getRefundsStatus();
        self::setValue($oBestitAmazonPay4OxidMain, '_sEditObjectId', 1);
        $oBestitAmazonPay4OxidMain->getRefundsStatus();
    }
}
