<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_oxEmail
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_oxEmail
 */
class bestitAmazonPay4OxidOxEmailTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param bestitAmazonPay4OxidContainer $oContainer
     *
     * @return bestitAmazonPay4Oxid_oxEmail
     * @throws ReflectionException
     */
    private function _getObject(bestitAmazonPay4OxidContainer $oContainer)
    {
        $oBestitAmazonPay4OxidOxEmail = new bestitAmazonPay4Oxid_oxEmail();
        $oContainer
            ->method('getLogger')
            ->willReturn(new NullLogger());

        self::setValue($oBestitAmazonPay4OxidOxEmail, '_oContainer', $oContainer);

        $oSmarty = $this->getMockBuilder(Smarty::class)->getMock();
        $oSmarty->expects($this->any())
            ->method('get_template_vars')
            ->willReturn(array());

        self::setValue(
            $oBestitAmazonPay4OxidOxEmail,
            '_oSmarty',
            $oSmarty
        );

        return $oBestitAmazonPay4OxidOxEmail;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidOxEmail = new bestitAmazonPay4Oxid_oxEmail();
        self::assertInstanceOf('bestitAmazonPay4Oxid_oxEmail', $oBestitAmazonPay4OxidOxEmail);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidOxEmail = new bestitAmazonPay4Oxid_oxEmail();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidOxEmail, '_getContainer')
        );
    }

    /**
     * @param string $sMethod
     * @param string $sSubject
     * @throws ReflectionException
     */
    private function _sendMailTest($sMethod, $sSubject)
    {
        $oContainer = $this->_getContainerMock();

        $oOrder = $this->_getOrderMock();
        $oOrder->expects($this->exactly(3))
            ->method('getFieldData')
            ->withConsecutive(
                array('oxbillfname'),
                array('oxbilllname'),
                array('oxbillemail')
            )
            ->will($this->onConsecutiveCalls(
                array('fName'),
                array('lName'),
                array('mail')
            ));

        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->once())
            ->method('getTemplateDir')
            ->with(false)
            ->will($this->returnValue('templateDir'));

        $oConfig->expects($this->exactly(2))
            ->method('setAdminMode')
            ->withConsecutive(
                array(false),
                array(true)
            );

        $oContainer->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        $oLoginClient = $this->_getLoginClientMock();

        $oLoginClient->expects($this->once())
            ->method('getOrderLanguageId')
            ->with($oOrder)
            ->will($this->returnValue(1));

        $oContainer->expects($this->once())
            ->method('getLoginClient')
            ->will($this->returnValue($oLoginClient));

        $oLanguage = $this->_getLanguageMock();

        $oLanguage->expects($this->exactly(2))
            ->method('getTplLanguage')
            ->will($this->returnValue(2));

        $oLanguage->expects($this->exactly(2))
            ->method('setTplLanguage')
            ->withConsecutive(
                array(1),
                array(2)
            );

        $oLanguage->expects($this->exactly(2))
            ->method('setBaseLanguage')
            ->withConsecutive(
                array(1),
                array(2)
            );

        $oLanguage->expects($this->once())
            ->method('translateString')
            ->with($sSubject)
            ->will($this->returnValue('translatedSubject'));

        $oContainer->expects($this->once())
            ->method('getLanguage')
            ->will($this->returnValue($oLanguage));

        $oBestitAmazonPay4OxidOxEmail = $this->_getObject($oContainer);
        $oBestitAmazonPay4OxidOxEmail->{$sMethod}($oOrder);
    }

    /**
     * @group unit
     * @covers ::sendAmazonInvalidPaymentEmail()
     * @covers ::_baseMailSetup()
     * @throws ReflectionException
     */
    public function testSendAmazonInvalidPaymentEmail()
    {
        $this->_sendMailTest(
            'sendAmazonInvalidPaymentEmail',
            'BESTITAMAZONPAY_EMAIL_SUBJECT_INVALID_PAYMENT'
        );
    }

    /**
     * @group unit
     * @covers ::sendAmazonRejectedPaymentEmail()
     * @covers ::_baseMailSetup()
     * @throws ReflectionException
     */
    public function testSendAmazonRejectedPaymentEmail()
    {
        $this->_sendMailTest(
            'sendAmazonRejectedPaymentEmail',
            'BESTITAMAZONPAY_EMAIL_SUBJECT_REJECTED_PAYMENT'
        );
    }
}
