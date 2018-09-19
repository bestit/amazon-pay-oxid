<?php

require_once dirname(__FILE__).'/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Class bestitAmazonPay4OxidTest
 * @coversDefaultClass bestitAmazonPay4OxidAddressUtil
 */
class bestitAmazonPay4OxidAddressUtilTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * @param oxConfig          $oConfig
     * @param DatabaseInterface $oDatabase
     * @param oxLang            $oLanguage
     *
     * @return bestitAmazonPay4OxidAddressUtil
     * @throws ReflectionException
     */
    private function _getObject(oxConfig $oConfig, DatabaseInterface $oDatabase, oxLang $oLanguage)
    {
        $oBestitAmazonPay4OxidAddressUtil = new bestitAmazonPay4OxidAddressUtil();
        self::setValue($oBestitAmazonPay4OxidAddressUtil, '_oConfigObject', $oConfig);
        self::setValue($oBestitAmazonPay4OxidAddressUtil, '_oDatabaseObject', $oDatabase);
        self::setValue($oBestitAmazonPay4OxidAddressUtil, '_oLanguageObject', $oLanguage);

        return $oBestitAmazonPay4OxidAddressUtil;
    }

    /**
     * @group unit
     */
    public function testCreateInstance()
    {
        $oBestitAmazonPay4OxidAddressUtil = new bestitAmazonPay4OxidAddressUtil();
        self::assertInstanceOf('bestitAmazonPay4OxidAddressUtil', $oBestitAmazonPay4OxidAddressUtil);
    }

    /**
     * @group  unit
     * @covers ::parseAmazonAddress()
     * @covers ::_parseAddressFields()
     * @covers ::_parseSingleAddress()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testParseAmazonAddress()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(17))
            ->method('isUtf')
            ->will($this->onConsecutiveCalls(true, true, true, true, false));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->exactly(5))
            ->method('quote')
            ->with('countryCode')
            ->will($this->returnValue('\'countryCode\''));

        $oDatabase->expects($this->exactly(5))
            ->method('getOne')
            ->with(new MatchIgnoreWhitespace(
                "SELECT OXID
                FROM oxv_oxcountry_de
                WHERE OXISOALPHA2 = 'countryCode'"
            ))
            ->will($this->returnValue('countryId'));

        $oLanguage = $this->_getLanguageMock();
        $oLanguage->expects($this->exactly(12))
            ->method('translateString')
            ->with('charset')
            ->will($this->returnValue('ISO-8859-1//TRANSLIT'));

        $oBestitAmazonPay4OxidAddressUtil = $this->_getObject(
            $oConfig,
            $oDatabase,
            $oLanguage
        );

        $oAmazonAddress = new stdClass();
        $oAmazonAddress->Name = 'FName MName LName';
        $oAmazonAddress->CountryCode = 'countryCode';

        self::assertEquals(
            array(
                'Name' => 'FName MName LName',
                'CountryCode' => 'countryCode',
                'LastName' => 'LName',
                'FirstName' => 'FName MName',
                'CountryId' => 'countryId',
                'CompanyName' => '',
                'Street' => '',
                'StreetNr' => '',
                'AddInfo' => ''
            ),
            $oBestitAmazonPay4OxidAddressUtil->parseAmazonAddress($oAmazonAddress)
        );

        $oAmazonAddress->AddressLine1 = 'Street Name 2a';
        self::assertEquals(
            array(
                'Name' => 'FName MName LName',
                'CountryCode' => 'countryCode',
                'LastName' => 'LName',
                'FirstName' => 'FName MName',
                'CountryId' => 'countryId',
                'CompanyName' => '',
                'Street' => 'Street Name',
                'StreetNr' => '2a',
                'AddInfo' => '',
                'AddressLine1' => 'Street Name 2a'
            ),
            $oBestitAmazonPay4OxidAddressUtil->parseAmazonAddress($oAmazonAddress)
        );

        $oAmazonAddress->AddressLine2 = 'Address Line 2 (Two)';
        self::assertEquals(
            array(
                'Name' => 'FName MName LName',
                'CountryCode' => 'countryCode',
                'LastName' => 'LName',
                'FirstName' => 'FName MName',
                'CountryId' => 'countryId',
                'CompanyName' => 'Address Line 2 (Two)',
                'Street' => 'Street Name',
                'StreetNr' => '2a',
                'AddInfo' => '',
                'AddressLine1' => 'Street Name 2a',
                'AddressLine2' => 'Address Line 2 (Two)'
            ),
            $oBestitAmazonPay4OxidAddressUtil->parseAmazonAddress($oAmazonAddress)
        );

        $oAmazonAddress->AddressLine3 = 'Address Line 3 (Three) €';
        self::assertEquals(
            array(
                'Name' => 'FName MName LName',
                'CountryCode' => 'countryCode',
                'LastName' => 'LName',
                'FirstName' => 'FName MName',
                'CountryId' => 'countryId',
                'CompanyName' => 'Address Line 2 (Two), Address Line 3 (Three) €',
                'Street' => 'Street Name',
                'StreetNr' => '2a',
                'AddInfo' => '',
                'AddressLine1' => 'Street Name 2a',
                'AddressLine2' => 'Address Line 2 (Two)',
                'AddressLine3' => 'Address Line 3 (Three) €'
            ),
            $oBestitAmazonPay4OxidAddressUtil->parseAmazonAddress($oAmazonAddress)
        );

        $oAmazonAddress->AddressLine3 = 'Address Line 3 (Three) €';
        self::assertEquals(
            array(
                'Name' => 'FName MName LName',
                'CountryCode' => 'countryCode',
                'LastName' => 'LName',
                'FirstName' => 'FName MName',
                'CountryId' => 'countryId',
                'CompanyName' => 'Address Line 2 (Two), Address Line 3 (Three) EUR',
                'Street' => 'Street Name',
                'StreetNr' => '2a',
                'AddInfo' => '',
                'AddressLine1' => 'Street Name 2a',
                'AddressLine2' => 'Address Line 2 (Two)',
                'AddressLine3' => 'Address Line 3 (Three) EUR'
            ),
            $oBestitAmazonPay4OxidAddressUtil->parseAmazonAddress($oAmazonAddress)
        );
    }

    /**
     * @group  unit
     * @covers ::encodeString()
     * @throws ReflectionException
     */
    public function testEncodeString()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(2))
            ->method('isUtf')
            ->will($this->onConsecutiveCalls(true, false));

        $oLanguage = $this->_getLanguageMock();
        $oLanguage->expects($this->once())
            ->method('translateString')
            ->with('charset')
            ->will($this->returnValue('ISO-8859-1//TRANSLIT'));

        $oBestitAmazonPay4OxidAddressUtil = $this->_getObject(
            $oConfig,
            $this->_getDatabaseMock(),
            $oLanguage
        );

        self::assertEquals('string', $oBestitAmazonPay4OxidAddressUtil->encodeString('string'));
        self::assertEquals('EUR', $oBestitAmazonPay4OxidAddressUtil->encodeString('€'));
    }
}
