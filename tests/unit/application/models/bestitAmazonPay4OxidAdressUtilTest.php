<?php

use Psr\Log\NullLogger;

require_once dirname(__FILE__) . '/../../bestitAmazon4OxidUnitTestCase.php';

/**
 * Unit test for class bestitAmazonPay4OxidAddressUtil
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 * @coversDefaultClass bestitAmazonPay4OxidAddressUtil
 */
class bestitAmazonPay4OxidAddressUtilTest extends bestitAmazon4OxidUnitTestCase
{
    /**
     * Started object to test.
     *
     * Filled by the setup method.
     *
     * @var bestitAmazonPay4OxidAddressUtil|null
     */
    private $fixture;

    /**
     * The parsed country id which is used in self::testParseAmazonAddress.
     *
     * @var string|null
     */
    private $parsedCountryId;

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
        $oBestitAmazonPay4OxidAddressUtil->setLogger(new NullLogger());
        self::setValue($oBestitAmazonPay4OxidAddressUtil, '_oConfigObject', $oConfig);
        self::setValue($oBestitAmazonPay4OxidAddressUtil, '_oDatabaseObject', $oDatabase);
        self::setValue($oBestitAmazonPay4OxidAddressUtil, '_oLanguageObject', $oLanguage);

        return $oBestitAmazonPay4OxidAddressUtil;
    }

    /**
     * Creates the fixture for a test of the full address parsing.
     *
     * @param bool $withLine2AsNormalStreet Street ordering like it is usual in germany.
     * @param bool $isUtf8
     *
     * @return void
     * @throws ReflectionException
     *
     * @todo Rename this things.
     */
    private function _loadFixtureForFillAddressParsingTest($withLine2AsNormalStreet = false, $isUtf8 = true)
    {
        $config = $this->_getConfigMock();
        $countriesWithLine2AsStreet = array('DE', 'AT', 'FR');

        if ($withLine2AsNormalStreet) {
            $countriesWithLine2AsStreet[] = 'countryCode';
        }

        $config->expects($this->any())
            ->method('isUtf')
            ->will($this->returnValue($isUtf8));

        $config->expects($this->any())
            ->method('getConfigParam')
            ->with($this->equalTo('aAmazonReverseOrderCountries'))
            ->will($this->returnCallback(
                function ($parameter) use ($countriesWithLine2AsStreet) {
                    if ($parameter === 'aAmazonReverseOrderCountries') {
                        return $countriesWithLine2AsStreet;
                    }
                }
            ));

        $oDatabase = $this->_getDatabaseMock();
        $oDatabase->expects($this->any())
            ->method('quote')
            ->with('countryCode')
            ->will($this->returnValue("'countryCode'"));

        $oDatabase->expects($this->any())
            ->method('getOne')
            ->with(new MatchIgnoreWhitespace(
                "SELECT OXID
                FROM oxv_oxcountry_de
                WHERE OXISOALPHA2 = 'countryCode'"
            ))
            ->will($this->returnValue($this->parsedCountryId = uniqid()));

        $oLanguage = $this->_getLanguageMock();
        $oLanguage->expects($this->any())
            ->method('translateString')
            ->with('charset')
            ->will($this->returnValue('ISO-8859-1//TRANSLIT'));

        $this->fixture = $this->_getObject(
            $config,
            $oDatabase,
            $oLanguage
        );
    }

    /**
     * Returns value to check the full address parsing.
     *
     * @see testParseAmazonAddress()
     *
     * @return array The first value is the imaginary data from amazon, the second one are the changed and added values,
     *              the third value marks if the company is handled first (like usual in germany) and the last value
     *              says, if utf8 is supported.
     */
    public function getFullAddressParseAsserts()
    {
        return array(
            'without address lines' => array(
                array(
                    'Name' => 'FName MName LName',
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => '',
                    'StreetNr' => '',
                    'AddInfo' => ''
                )
            ),
            'with only address line 1' => array(
                array(
                    'AddressLine1' => 'Address Line 1a',
                    'Name' => 'FName MName LName',
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => 'Address Line',
                    'StreetNr' => '1a',
                    'AddInfo' => '',
                )
            ),
            'with address line 1 and 2, normal order' => array(
                array(
                    'AddressLine1' => 'Address Line 1a',
                    'AddressLine2' => 'Address Line 2 (Two)',
                    'Name' => 'FName MName LName',
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Address Line 2 (Two)',
                    'Street' => 'Address Line',
                    'StreetNr' => '1a',
                    'AddInfo' => '',
                )
            ),
            'with address line 1 and 2, reversed order' => array(
                array(
                    'AddressLine1' => 'Address Line 1a',
                    'AddressLine2' => 'Address Line 2 (Two)',
                    'Name' => 'FName MName LName',
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Address Line 1a',
                    'Street' => 'Address Line',
                    'StreetNr' => '2',
                    'AddInfo' => '(Two)',
                ),
                true
            ),
            'with address line 1, 2, 3 with street first' => array(
                array(
                    'AddressLine1' => 'Address Line 1a',
                    'AddressLine2' => 'Address Line 2 (Two)',
                    'AddressLine3' => 'Address Line 3 (Three) €',
                    'Name' => 'FName MName LName',
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Address Line 2 (Two), Address Line 3 (Three) €',
                    'Street' => 'Address Line',
                    'StreetNr' => '1a',
                    'AddInfo' => '',
                )
            ),
            'with address line 1, 2, 3 with company first' => array( // fill company first
                array(
                    'AddressLine1' => 'Address Line 1a',
                    'AddressLine2' => 'Address Line 2 (Two)',
                    'AddressLine3' => 'Address Line 3 (Three) €',
                    'Name' => 'FName MName LName',
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Address Line 1a, Address Line 3 (Three) €',
                    'Street' => 'Address Line',
                    'StreetNr' => '2',
                    'AddInfo' => '(Two)',
                ),
                true
            ),
            'with address line 1, 2, 3 with company first, but without utf8' => array(
                array(
                    'AddressLine1' => 'Address Line 1a',
                    'AddressLine2' => 'Address Line 2 (Two)',
                    'AddressLine3' => 'Address Line 3 (Three) €',
                    'Name' => 'FName MName LName',
                ),
                array(
                    'AddressLine3' => 'Address Line 3 (Three) EUR',
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Address Line 1a, Address Line 3 (Three) EUR',
                    'Street' => 'Address Line',
                    'StreetNr' => '2',
                    'AddInfo' => '(Two)',
                ),
                true,
                false
            ),
            // Amazon had a short period, in which their address form was wrong
            // so the street number must be handled specially.
            'map pseudo code with all filled fields, company on top like in germany, but broken street nr' => array(
                array(
                    'AddressLine1' => 'Street Line 1a additional',
                    'AddressLine2' => ' 50N',
                    'AddressLine3' => 'Company Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Company Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => 'additional 50N',
                ),
                true
            ),
            'map pseudo code with company and street, company on top like in germany, but broken street nr' => array(
                array(
                    'AddressLine1' => 'Street Line',
                    'AddressLine2' => ' 50N',
                    'AddressLine3' => 'Company Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Company Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '50N',
                    'AddInfo' => '',
                ),
                true
            ),
            'map pseudo code with just street, company on top like in germany, but broken street nr' => array(
                array(
                    'AddressLine1' => 'Street Line',
                    'AddressLine2' => ' 50N',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => 'Street Line',
                    'StreetNr' => '50N',
                    'AddInfo' => '',
                ),
                true
            ),
            'map pseudo code with all filled fields, company on top like in germany' => array(
                array(
                    'AddressLine1' => 'Company Line 1',
                    'AddressLine2' => 'Street Line 2a additional',
                    'AddressLine3' => 'Info Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Company Line 1, Info Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '2a',
                    'AddInfo' => 'additional',
                ),
                true
            ),
            'map pseudo code without line 2, company on top like in germany' => array(
                array(
                    'AddressLine1' => 'Street Line 1a additional',
                    'AddressLine3' => 'Info Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Info Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => 'additional',
                ),
                true
            ),
            'map pseudo code without line 2 and 3, company on top like in germany' => array(
                array(
                    'AddressLine1' => 'Street Line 1a',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => '',
                ),
                true
            ),
            'map pseudo code with all filled fields, normal order' => array(
                array(
                    'AddressLine1' => 'Street Line 1a additional',
                    'AddressLine2' => 'Company Line 2',
                    'AddressLine3' => 'Info Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Company Line 2, Info Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => 'additional',
                )
            ),
            'map pseudo code with all filled fields but no info line, normal order' => array(
                array(
                    'AddressLine1' => 'Street Line 1a additional',
                    'AddressLine2' => 'Company Line 2',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Company Line 2',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => 'additional',
                )
            ),
            'map pseudo code with line 1 but no company line, normal order' => array(
                array(
                    'AddressLine1' => 'Street Line 1a additional',
                    'AddressLine2' => '',
                    'AddressLine3' => 'Info Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Info Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => 'additional',
                )
            ),
            'map pseudo code with line 1 but nothing else, normal order' => array(
                array(
                    'AddressLine1' => 'Street Line 1a additional',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => 'Street Line',
                    'StreetNr' => '1a',
                    'AddInfo' => 'additional',
                )
            ),
            'map pseudo code with all filled fields but line 1, normal order' => array(
                array(
                    'AddressLine2' => 'Street Line 2b additional',
                    'AddressLine3' => 'Company Line 3',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => 'Company Line 3',
                    'Street' => 'Street Line',
                    'StreetNr' => '2b',
                    'AddInfo' => 'additional',
                )
            ),
            'map pseudo code with just line 2, normal order' => array(
                array(
                    'AddressLine2' => 'Street Line 2b',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => 'Street Line',
                    'StreetNr' => '2b',
                    'AddInfo' => '',
                )
            ),
            'map pseudo code with just line 3, normal order' => array(
                array(
                    'AddressLine2' => 'StreetLine 3c',
                    'Name' => 'FName MName LName'
                ),
                array(
                    'LastName' => 'LName',
                    'FirstName' => 'FName MName',
                    'CompanyName' => '',
                    'Street' => 'StreetLine',
                    'StreetNr' => '3c',
                    'AddInfo' => '',
                )
            )
        );
    }

    /**
     * Returns assert to check if the single address line gets parsed correctly.
     *
     * @return array
     */
    public function getParseSingleAddressAsserts()
    {
        return array(
            // test desc => Test value, country, result array.
            'Test german address' => array('Teststraße 1', 'DE', array('Street' => 'Teststraße', 'StreetNr' => '1')),
            'Test german address, no whitespace' => array(
                'Teststreet1a',
                'DE',
                array('Street' => 'Teststreet', 'StreetNr' => '1a')
            ),
            'Test german address with add info' => array(
                'Teststreet 1a addinfo',
                'DE',
                array('Street' => 'Teststreet', 'StreetNr' => '1a', 'AddInfo' => 'addinfo')
            ),
            'Test german separated address with add info' => array(
                'Test street 1a addinfo',
                'DE',
                array('Street' => 'Test street', 'StreetNr' => '1a', 'AddInfo' => 'addinfo')
            ),
            'Test german address with add info no whitespace' => array(
                'Teststreet1 addinfo',
                'DE',
                array('Street' => 'Teststreet', 'StreetNr' => '1', 'AddInfo' => 'addinfo')
            ),
            'Test address format "street streetnumber" without streetnumber' => array(
                'Teststreet',
                'DE',
                array('Street' => 'Teststreet')
            ),
            'Test FR address without streetnumber' => array(
                'Teststreet',
                'FR',
                array('Street' => 'Teststreet', 'StreetNr' => '')
            ),
            'Test FR address' => array(
                '1a Teststreet',
                'FR',
                array('Street' => 'Teststreet', 'StreetNr' => '1a')
            ),
            'Test FR address no whitespace' => array(
                '1Teststreet',
                'FR',
                array('Street' => 't', 'StreetNr' => '1Teststree')
            ),
            'Test FR address no whitespace with add info' => array(
                '1Teststreet addInfo',
                'FR',
                array('StreetNr' => '1Teststreet', 'Street' => 'addInfo')
            )
        );
    }

    /**
     * Sets up the test.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->fixture = new bestitAmazonPay4OxidAddressUtil();

        $this->fixture->setLogger(new NullLogger());
    }

    /**
     * Checks if the object has the correct parent to secure an api.
     *
     * @group unit
     *
     * @return void
     */
    public function testInheritanceOfObject()
    {
        $oBestitAmazonPay4OxidAddressUtil = new bestitAmazonPay4OxidAddressUtil();

        self::assertInstanceOf('bestitAmazonPay4OxidContainer', $this->fixture);
    }

    /**
     * Checks if the required interface is registered.
     *
     * @return void
     */
    public function testInterfacesOfObject()
    {
        self::assertInstanceOf('\Psr\Log\LoggerAwareInterface', $this->fixture);
    }

    /**
     * Checks the full parsing of an address.
     *
     * @dataProvider getFullAddressParseAsserts
     * @group unit
     * @covers ::parseAmazonAddress()
     *
     * @throws oxConnectionException
     * @throws ReflectionException
     *
     * @param array $originalAmazonData
     * @param array $parsedAmazonData
     * @param bool $withLine2AsNormalStreet Street ordering like it is usual in germany.
     * @param bool $isUtf8
     *
     * @return void
     */
    public function testParseAmazonAddress(
        array $originalAmazonData,
        array $parsedAmazonData,
        $withLine2AsNormalStreet = false,
        $isUtf8 = true
    ) {
        $this->_loadFixtureForFillAddressParsingTest($withLine2AsNormalStreet, $isUtf8);

        $amazonAddress = new stdClass();
        $amazonAddress->CountryCode = 'countryCode';

        foreach ($originalAmazonData as $field => $value) {
            $amazonAddress->$field = $value;
        }

        $parsedAmazonData['CountryCode'] = 'countryCode';
        $parsedAmazonData['CountryId'] = $this->parsedCountryId;

        self::assertEquals(
            $parsedAmazonData + $originalAmazonData,
            $this->fixture->parseAmazonAddress($amazonAddress)
        );
    }

    /**
     * Tests if the given address lines are parsed correctly.
     *
     * @covers ::_parseSingleAddress()
     * @dataProvider getParseSingleAddressAsserts
     * @throws ReflectionException
     *
     * @param string $addressLine
     * @param string $countryIso
     * @param array $checkedValues The result array of the parsing.
     *
     * @return void
     */
    public function test_parseSingleAddress($addressLine, $countryIso, array $checkedValues)
    {
        $testResult = $this->callMethod(
            $this->fixture,
            '_parseSingleAddress',
            array($addressLine, $countryIso)
        );

        self::assertTrue(is_array($testResult));

        $requiredFields = array('Street', 'StreetNr', 'AddInfo');

        foreach ($checkedValues as $field => $value) {
            self::assertArrayHasKey(
                $field,
                $testResult,
                sprintf('The parsed field %s is missing.', $field)
            );

            self::assertSame(
                $value,
                $testResult[$field],
                sprintf('The value of field %s does not match.', $field)
            );

            unset($requiredFields[array_search($field, $requiredFields)]);
        }

        foreach ($requiredFields as $requiredField) {
            self::assertArrayHasKey(
                $requiredField,
                $testResult,
                sprintf('The required field %s is missing.', $requiredField)
            );

            self::assertSame(
                '',
                $testResult[$requiredField],
                sprintf('The value of field %s does not match.', $requiredField)
            );
        }
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
        $oBestitAmazonPay4OxidAddressUtil->setLogger(new NullLogger());

        self::assertEquals('string', $oBestitAmazonPay4OxidAddressUtil->encodeString('string'));
        self::assertEquals('EUR', $oBestitAmazonPay4OxidAddressUtil->encodeString('€'));
    }
}
