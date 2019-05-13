<?php

/**
 * Model to handle address parsing
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidAddressUtil extends bestitAmazonPay4OxidContainer
{
    /**
     * Returns parsed Street name and Street number in array
     *
     * @param string $sString Full address
     * @param string $sIsoCountryCode ISO2 code of country of address
     *
     * @return string
     */
    protected function _parseSingleAddress($sString, $sIsoCountryCode = null)
    {
        // Array of iso2 codes of countries that have address format <street_no> <street>
        $aStreetNoStreetCountries = $this->getConfig()->getConfigParam('aAmazonStreetNoStreetCountries');

        if (in_array($sIsoCountryCode, $aStreetNoStreetCountries)) {
            // matches streetname/streetnumber like "streetnumber streetname"
            preg_match('/\s*(?P<Number>\d[^\s]*)*\s*(?P<Name>[^\d]*[^\d\s])\s*(?P<AddInfo>.*)/', $sString, $aResult);
        } else {
            // default: matches streetname/streetnumber like "streetname streetnumber"
            preg_match('/\s*(?P<Name>[^\d]*[^\d\s])\s*((?P<Number>\d[^\s]*)\s*(?P<AddInfo>.*))*/', $sString, $aResult);
        }

        return $aResult;
    }

    /**
     * Parses the amazon address fields.
     *
     * @param \stdClass $oAmazonData
     * @param array     $aResult
     */
    protected function _parseAddressFields($oAmazonData, array &$aResult)
    {
        // Cleanup address fields and store them to an array
        $aAmazonAddresses = array(
            1 => is_string($oAmazonData->AddressLine1) ? trim($oAmazonData->AddressLine1) : '',
            2 => is_string($oAmazonData->AddressLine2) ? trim($oAmazonData->AddressLine2) : '',
            3 => is_string($oAmazonData->AddressLine3) ? trim($oAmazonData->AddressLine3) : ''
        );

        // Array of iso2 codes of countries that have another addressline order
        $aReverseOrderCountries = $this->getConfig()->getConfigParam('aAmazonReverseOrderCountries');

        $aMap = array_flip($aReverseOrderCountries);
        $aCheckOrder = isset($aMap[$oAmazonData->CountryCode]) === true ? array (2, 1) : array(1, 2);
        $sStreet = '';
        $sCompany = '';

        foreach ($aCheckOrder as $iCheck) {
            if ($aAmazonAddresses[$iCheck] !== '') {
                if ($sStreet !== '') {
                    $sCompany = $aAmazonAddresses[$iCheck];
                    break;
                }

                $sStreet = $aAmazonAddresses[$iCheck];
            }
        }

        if ($aAmazonAddresses[3] !== '') {
            $sCompany = ($sCompany === '') ? $aAmazonAddresses[3] : "{$sCompany}, {$aAmazonAddresses[3]}";
        }

        $aResult['CompanyName'] = $sCompany;

        $aAddress = $this->_parseSingleAddress($sStreet, $oAmazonData->CountryCode);
        $aResult['Street'] = isset($aAddress['Name']) === true ? $aAddress['Name'] : '';
        $aResult['StreetNr'] = isset($aAddress['Number']) === true ? $aAddress['Number'] : '';
        $aResult['AddInfo'] = isset($aAddress['AddInfo']) === true ? $aAddress['AddInfo'] : '';
    }

    /**
     * Returns Parsed address from Amazon by specific rules
     *
     * @param object $oAmazonData Address object
     *
     * @return array Parsed Address
     * @throws oxConnectionException
     */
    public function parseAmazonAddress($oAmazonData)
    {
        //Cast to array
        $aResult = (array)$oAmazonData;

        //Parsing first and last names
        $aFullName = explode(' ', trim($oAmazonData->Name));
        $aResult['LastName'] = array_pop($aFullName);
        $aResult['FirstName'] = implode(' ', $aFullName);

        $sTable = getViewName('oxcountry');
        $oAmazonData->CountryCode = (string)$oAmazonData->CountryCode === 'UK' ? 'GB' : $oAmazonData->CountryCode;
        $sSql = "SELECT OXID
            FROM {$sTable}
            WHERE OXISOALPHA2 = ".$this->getDatabase()->quote($oAmazonData->CountryCode);

        //Country ID
        $aResult['CountryId'] = $this->getDatabase()->getOne($sSql);

        //Parsing address
        $this->_parseAddressFields($oAmazonData, $aResult);

        //If shop runs in non UTF-8 mode encode values to ANSI
        if ($this->getConfig()->isUtf() === false) {
            foreach ($aResult as $sKey => $sValue) {
                $aResult[$sKey] = $this->encodeString($sValue);
            }
        }

        return $aResult;
    }


    /**
     * If shop is using non-Utf8 chars, encode string according used encoding
     *
     * @param string $sString the string to encode
     *
     * @return string encoded string
     */
    public function encodeString($sString)
    {
        //If shop is running in UTF-8 nothing to do here
        if ($this->getConfig()->isUtf() === true) {
            return $sString;
        }

        $sShopEncoding = $this->getLanguage()->translateString('charset');
        return iconv('UTF-8', $sShopEncoding, $sString);
    }
}
