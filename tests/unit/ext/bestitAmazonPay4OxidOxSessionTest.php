<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4Oxid_oxSession
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4Oxid_oxSession
 */
class bestitAmazonPay4OxidOxSessionTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @group unit
     * @covers ::_getRequireSessionWithParams()
     * @throws ReflectionException
     */
    public function testGetRequireSessionWithParams()
    {
        $oBestitAmazonPay4OxidOxSession = new bestitAmazonPay4Oxid_oxSession_Test();
        self::callMethod($oBestitAmazonPay4OxidOxSession, '_getRequireSessionWithParams');
        $aParams = $oBestitAmazonPay4OxidOxSession::getRequireParams();
        self::assertTrue($aParams['fnc']['amazonLogin']);
    }
}

class bestitAmazonPay4Oxid_oxSession_Test extends bestitAmazonPay4Oxid_oxSession
{
    public function getRequireParams(): array
    {
        return $this->_aRequireSessionWithParams;
    }
}