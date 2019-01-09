<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_module_config
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_module_config
 */
class bestitAmazonPay4OxidModuleConfigTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidModuleConfig = new bestitAmazonPay4Oxid_module_config();
        self::assertInstanceOf('bestitAmazonPay4Oxid_module_config', $oBestitAmazonPay4OxidModuleConfig);
    }

    /**
     * @group unit
     * @covers ::_getContainer()
     * @throws ReflectionException
     */
    public function testGetContainer()
    {
        $oBestitAmazonPay4OxidModuleConfig = new bestitAmazonPay4Oxid_module_config();
        self::assertInstanceOf(
            'bestitAmazonPay4OxidContainer',
            self::callMethod($oBestitAmazonPay4OxidModuleConfig, '_getContainer')
        );
    }

    /**
     * @group unit
     * @covers ::saveConfVars()
     * @throws ReflectionException
     * @throws oxSystemComponentException
     * @throws oxSystemComponentException
     * @throws oxSystemComponentException
     */
    public function testSaveConfVars()
    {
        $oContainer = $this->_getContainerMock();

        $oConfig = $this->_getConfigMock();

        $oConfig->expects($this->exactly(2))
            ->method('getRequestParameter')
            ->with('bestitAmazonPay4OxidQuickConfig')
            ->will($this->onConsecutiveCalls(
                'invalidJson{',
                '{
                    "merchant_id": "merchantId",
                    "access_key": "accessKey",
                    "secret_key": "secretKey",
                    "client_id": "clientId",
                    "client_secret": "clientSecret"
                }'
            ));

        $oContainer->expects($this->exactly(2))
            ->method('getConfig')
            ->will($this->returnValue($oConfig));

        /** @var PHPUnit_Framework_MockObject_MockObject|bestitAmazonPay4Oxid_module_config $oBestitAmazonPay4OxidModuleConfig */
        $oBestitAmazonPay4OxidModuleConfig = $this->getMock(
            'bestitAmazonPay4Oxid_module_config',
            array('getEditObjectId', '_parentSaveConfVars')
        );
        self::setValue($oBestitAmazonPay4OxidModuleConfig, '_oContainer', $oContainer);

        $oBestitAmazonPay4OxidModuleConfig->expects($this->exactly(3))
            ->method('getEditObjectId')
            ->will($this->onConsecutiveCalls('someId', 'bestitamazonpay4oxid', 'bestitamazonpay4oxid'));

        $oBestitAmazonPay4OxidModuleConfig->expects($this->exactly(3))
            ->method('_parentSaveConfVars');

        $aPostBackup = $_POST;
        $aDummyPostData = array(
            'confstrs' => array(
                'sAmazonSellerId' => 'sAmazonSellerIdOld',
                'sAmazonAWSAccessKeyId' => 'sAmazonAWSAccessKeyIdOld',
                'sAmazonLoginClientId' => 'sAmazonLoginClientIdOld',
            ),
            'confpassword' => array(
                'sAmazonSignature' => 'sAmazonSignatureOld'
            )
        );
        $_POST = $aDummyPostData;

        $oBestitAmazonPay4OxidModuleConfig->saveConfVars();
        self::assertEquals($aDummyPostData, $_POST);

        $oBestitAmazonPay4OxidModuleConfig->saveConfVars();
        self::assertEquals($aDummyPostData, $_POST);

        $oBestitAmazonPay4OxidModuleConfig->saveConfVars();
        self::assertEquals(
            array(
                'confstrs' => array(
                    'sAmazonSellerId' => 'merchantId',
                    'sAmazonAWSAccessKeyId' => 'accessKey',
                    'sAmazonLoginClientId' => 'clientId',
                ),
                'confpassword' => array(
                    'sAmazonSignature' => 'secretKey'
                )
            ),
            $_POST
        );

        $_POST = $aPostBackup;
    }
}
