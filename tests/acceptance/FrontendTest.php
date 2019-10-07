<?php

if (class_exists('oxAcceptanceTestCase') === false) {
    return;
}

/**
 * Test for frontend integration
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class FrontendTest extends oxAcceptanceTestCase
{
    /**
     * Adds configuration data for testing
     */
    protected function setUp()
    {
        parent::setUp();
        $aConfigData = $this->getAmazonPaySettings();

        if (is_array($aConfigData) && !empty($aConfigData)) {
            $this->callShopSC('oxConfig', null, null, $aConfigData);
        }
    }

    /**
     * Reset columns if the exits.
     *
     * @param int $shopId
     */
    public function activateModules($shopId = 1)
    {
        if (method_exists($this, 'executeSql')) {
            $aTableColumns = array(
                'BESTITAMAZONORDERREFERENCEID' => 'oxorder',
                'BESTITAMAZONAUTHORIZATIONID' => 'oxorder',
                'BESTITAMAZONCAPTUREID' => 'oxorder',
                'BESTITAMAZONID' => 'oxuser',
            );

            foreach ($aTableColumns as $sColumn => $sTable) {
                try {
                    $this->executeSql("ALTER TABLE `{$sTable}` DROP `{$sColumn}`");
                } catch (Throwable $oException) {
                    // Do nothing
                }
            }
        }

        parent::activateModules($shopId);
    }

    /**
     * Returns configuration data for tests
     *
     * @return array
     */
    private function getAmazonPaySettings()
    {
        return $this->getArrayFromFile(__DIR__ .'/config_data.php');
    }

    /**
     * Returns data array from file
     *
     * @param $sFilePath
     *
     * @return array
     */

    private function getArrayFromFile($sFilePath)
    {
        $aData = array();

        if (file_exists($sFilePath)) {
            $aData = include $sFilePath;
        }
        return $aData;
    }

    /**
     * @group acceptance
     */
    public function testBasketBtnNextTopBlock()
    {
        $this->addToBasket('05848170643ab0deb9914566391c0c63');
        $page = $this->getMinkSession()->getPage();
        self::assertRegExp('/.*out\/src\/js\/bestitamazonpay4oxid.js.*/', $page->getContent());
        self::assertRegExp('/.*out\/src\/css\/bestitamazonpay4oxid.css.*/', $page->getContent());
        self::assertRegExp('/.*class\="amazonContentGroup".*/', $page->getContent());
        self::assertRegExp('/.*OffAmazonPayments\.Button.*/', $page->getContent());
    }

    /**
     * @group acceptance
     */
    public function testSelectPaymentBlock()
    {
        $this->addToBasket('05848170643ab0deb9914566391c0c63');
        $aParams = array(
            'cl' => 'account',
            'fnc' => 'login_noredirect',
            'lgn_usr' => 'admin',
            'lgn_pwd' => 'admin'
        );
        $this->openNewWindow($this->_getShopUrl($aParams, null), false);
        $this->open($this->getTestConfig()->getShopUrl().'?cl=payment');
        $page = $this->getMinkSession()->getPage();
        self::assertRegExp('/.*id\="payment_bestitamazon".*/', $page->getContent());
        self::assertRegExp('/.*class\="amazonContentGroup".*/', $page->getContent());
    }

    /**
     * @group acceptance
     */
    public function testCheckoutPaymentNextStepBlock()
    {
        $this->addToBasket('05848170643ab0deb9914566391c0c63');
        $this->open($this->getTestConfig()->getShopUrl().'?cl=user');
        $page = $this->getMinkSession()->getPage();
        self::assertRegExp('/.*out\/src\/js\/bestitamazonpay4oxid.js.*/', $page->getContent());
        self::assertRegExp('/.*out\/src\/css\/bestitamazonpay4oxid.css.*/', $page->getContent());
        self::assertRegExp('/.*id\="amazonPayButtonLine".*/', $page->getContent());
        self::assertRegExp('/.*class\="amazonContentGroup".*/', $page->getContent());
        self::assertRegExp('/.*OffAmazonPayments\.Button.*/', $page->getContent());
    }
    /**
     * @group acceptance
     */
    public function testFooterMainBlock()
    {
        $this->open($this->getTestConfig()->getShopUrl());
        $page = $this->getMinkSession()->getPage();
        self::assertRegExp('/.*out\/src\/js\/bestitamazonpay4oxid.js.*/', $page->getContent());
        self::assertRegExp('/.*out\/src\/css\/bestitamazonpay4oxid.css.*/', $page->getContent());
        self::assertRegExp('/.*id\="amazonLoginButton".*/', $page->getContent());
        self::assertRegExp('/.*OffAmazonPayments\.Button.*/', $page->getContent());
    }
}
