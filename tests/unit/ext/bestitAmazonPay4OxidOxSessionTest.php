<?php

require_once dirname(__FILE__).'/../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidOrderOverviewTest
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
        $oBestitAmazonPay4OxidOxSession = new bestitAmazonPay4Oxid_oxSession();
        self::callMethod($oBestitAmazonPay4OxidOxSession, '_getRequireSessionWithParams');
        $aParams = self::readAttribute($oBestitAmazonPay4OxidOxSession, '_aRequireSessionWithParams');
        self::assertTrue($aParams['fnc']['amazonLogin']);
    }
}
