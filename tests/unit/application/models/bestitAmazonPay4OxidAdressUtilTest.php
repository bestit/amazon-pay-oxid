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
     * Returns assert to check if the single address line gets parsed correctly.
     *
     * @return array
     */
    public function getParseSingleAddressAsserts()
    {
        return array(
            // test desc => Test value, country, result array.
            'Test german address' => array('Teststraße 1', 'DE', array('Name' => 'Teststraße', 'Number' => '1')),
            'Test german address, no whitespace' => array(
                'Teststreet1a',
                'DE',
                array('Name' => 'Teststreet', 'Number' => '1a')
            ),
            'Test german address with add info' => array(
                'Teststreet 1a addinfo',
                'DE',
                array('Name' => 'Teststreet', 'Number' => '1a', 'AddInfo' => 'addinfo')
            ),
            'Test german separated address with add info' => array(
                'Test street 1a addinfo',
                'DE',
                array('Name' => 'Test street', 'Number' => '1a', 'AddInfo' => 'addinfo')
            ),
            'Test german address with add info no whitespace' => array(
                'Teststreet1 addinfo',
                'DE',
                array('Name' => 'Teststreet', 'Number' => '1', 'AddInfo' => 'addinfo')
            ),
            'Test address format "street streetnumber" without streetnumber' => array(
                'Teststreet',
                'DE',
                array('Name' => 'Teststreet')
            ),
            'Test FR address without streetnumber' => array(
                'Teststreet',
                'FR',
                array('Name' => 'Teststreet', 'Number' => '')
            ),
            'Test FR address' => array(
                '1a Teststreet',
                'FR',
                array('Name' => 'Teststreet', 'Number' => '1a')
            ),
            'Test FR address no whitespace' => array(
                '1Teststreet',
                'FR',
                array('Name' => 't', 'Number' => '1Teststree')
            ),
            'Test FR address no whitespace with add info' => array(
                '1Teststreet addInfo',
                'FR',
                array('Number' => '1Teststreet', 'Name' => 'addInfo')
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
     * @group  unit
     * @covers ::parseAmazonAddress()
     * @covers ::_parseAddressFields()
     * @throws oxConnectionException
     * @throws ReflectionException
     */
    public function testParseAmazonAddress()
    {
        $oConfig = $this->_getConfigMock();
        $oConfig->expects($this->exactly(17))
            ->method('isUtf')
            ->will($this->onConsecutiveCalls(true, true, true, true, false));

        $oConfig->expects($this->any())
            ->method('getConfigParam')
            ->with($this->equalTo('aAmazonReverseOrderCountries'))
            ->will($this->returnCallback(
                function($sParameter) {
                    if ($sParameter === 'aAmazonReverseOrderCountries') {
                        return array('DE', 'AT', 'FR');
                    }
                }
            ));

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

        $oBestitAmazonPay4OxidAddressUtil->setLogger(new NullLogger());

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

        foreach ($checkedValues as $field => $value) {
            self::assertArrayHasKey($field, $testResult);
            self::assertSame(
                $value,
                $testResult[$field],
                sprintf('The value of field %s does not match.', $field)
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
