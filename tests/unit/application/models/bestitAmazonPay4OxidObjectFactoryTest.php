<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4OxidObjectFactory
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4OxidObjectFactory
 */
class bestitAmazonPay4OxidObjectFactoryTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidObjectFactory = new bestitAmazonPay4OxidObjectFactory();
        self::assertInstanceOf('bestitAmazonPay4OxidObjectFactory', $oBestitAmazonPay4OxidObjectFactory);
    }

    /**
     * @group  unit
     * @covers ::createOxidObject()
     * @throws oxSystemComponentException
     */
    public function testCreateOxidObject()
    {
        $oBestitAmazonPay4OxidObjectFactory = new bestitAmazonPay4OxidObjectFactory();
        self::assertInstanceOf('oxOrder', $oBestitAmazonPay4OxidObjectFactory->createOxidObject('oxOrder'));
        self::assertInstanceOf('oxUser', $oBestitAmazonPay4OxidObjectFactory->createOxidObject('oxUser'));
    }

    /**
     * @group  unit
     * @covers ::createIpnHandler()
     */
    public function testCreateIpnHandler()
    {
        $body = '{
            "Type":"Notification",
            "Message":"Test",
            "MessageId":"Test",
            "Timestamp":"Test",
            "Subject":"Test",
            "TopicArn":"Test",
            "Signature":"Test",
            "SigningCertURL":"http://sns.us-east-1.amazonaws.com/SimpleNotificationService-bb750dd426d95ee9390147a5624348ee.pem"}
        ';

        $oBestitAmazonPay4OxidObjectFactory = new bestitAmazonPay4OxidObjectFactory();

        try {
            $oBestitAmazonPay4OxidObjectFactory->createIpnHandler(
                array('x-amz-sns-message-type' => 'Notification'),
                $body
            );
        } catch (\Exception $exception) {
            self::assertEquals('The certificate is located on an invalid domain.', $exception->getMessage());
        }
    }
}
