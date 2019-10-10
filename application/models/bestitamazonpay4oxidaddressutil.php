<?php

/**
 * Model to handle address parsing
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidAddressUtil extends bestitAmazonPay4OxidContainer
{
    /**
     * Return the parsing which returns more pattern hits and contains a longer street name.
     *
     * @param array $followingNumberMatches
     * @param array $leadingNumberMatches
     *
     * @return array
     */
    protected function _handleParsingForInconclusiveMatches(array $followingNumberMatches, array $leadingNumberMatches)
    {
        $leadingNumberMatchesCount = count($leadingNumberMatches);
        $followingNumberMatchesCount = count($followingNumberMatches);
        $relevantParsing = array();

        if ($leadingNumberMatchesCount === $followingNumberMatchesCount) {
            if ($this->_isStreetParsingLongerThanNumber($followingNumberMatches)) {
                $relevantParsing = $followingNumberMatches;
            }
        } else {
            $isFollowingNumberMatchMoreRelevant = $this->_isFollowingNumberMatchMoreRelevant(
                $followingNumberMatchesCount,
                $leadingNumberMatchesCount
            );

            $relevantParsing = $isFollowingNumberMatchMoreRelevant ? $followingNumberMatches : $leadingNumberMatches;
        }

        return $relevantParsing;
    }

    /**
     * If both parsings match exactly than the parsing was inconclusive.
     *
     * @param array|bool $followingNumberMatches
     * @param array|bool $leadingNumberMatches
     *
     * @return bool
     */
    protected function _isAdressLineParsingInconclusive($followingNumberMatches, $leadingNumberMatches)
    {
        return $leadingNumberMatches && $followingNumberMatches;
    }

    /**
     * Can a following  number be found?
     *
     * @param int $followingNumberMatchesCount
     * @param int $leadingNumberMatchesCount
     *
     * @return bool
     */
    protected function _isFollowingNumberMatchMoreRelevant($followingNumberMatchesCount, $leadingNumberMatchesCount)
    {
        return $followingNumberMatchesCount > $leadingNumberMatchesCount;
    }

    /**
     * A longer street name than the number suggests, that the parsing was correct.
     *
     * @param array $followingNumberMatches
     *
     * @return bool
     */
    protected function _isStreetParsingLongerThanNumber(array $followingNumberMatches)
    {
        return strlen((string) @$followingNumberMatches['Name']) > strlen((string) @$followingNumberMatches['Number']);
    }

    /**
     * Returns parsed Street name and Street number in array
     *
     * @param string $addressLine
     * @param string $iso2CountryCode
     *
     * @return array
     */
    protected function _parseSingleAddress($addressLine, $iso2CountryCode)
    {
        $leadingNumberMatches = $this->_searchForLeadingNumberInAddressLine($addressLine);
        $followingNumberMatches = $this->_searchForFollowingNumberInAddressLine($addressLine);

        $relevantParsing = $leadingNumberMatches;

        if ($this->_isAdressLineParsingInconclusive($followingNumberMatches, $leadingNumberMatches)) {
            $this->getLogger()->debug(
                'The address parsing was not conclusive.',
                array(
                    'countyCode' => $iso2CountryCode,
                    'originalAddressLine' => $addressLine
                )
            );

            $relevantParsing = $this->_handleParsingForInconclusiveMatches(
                $followingNumberMatches,
                $leadingNumberMatches
            );
        } else {
            $this->getLogger()->debug(
                'The address parsing was conclusive.',
                array(
                    'countyCode' => $iso2CountryCode,
                    'originalAddressLine' => $addressLine
                )
            );

            if ($followingNumberMatches) {
                $relevantParsing = $followingNumberMatches;
            }
        }
        // else is hidden thru the default value!

        $this->getLogger()->info(
            'Parsed the given single address line to a value array.',
            array(
                'countyCode' => $iso2CountryCode,
                'originalAddressLine' => $addressLine,
                'parsedAddressLine' => $relevantParsing
            )
        );

        return $relevantParsing;
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
        $aCheckOrder = isset($aMap[$oAmazonData->CountryCode]) === true ? array(2, 1) : array(1, 2);
        $sStreet = '';
        $sCompany = '';

        // TODO: Fix it with OXAP-292. Understanding this is not mentally-easy.
        // The break is used in "reversed order" (company is filled after the street),
        // this feels like "pfeil durch die brust ins auge."
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

        $this->getLogger()->debug(
            'Amazon address parsed',
            array('result' => $aResult, 'amazonAddress' => $aAmazonAddresses)
        );
    }

    /**
     * Matches a pattern with a following possible number (as a string) against the given address line.
     *
     * @param string $addressLine
     *
     * @return bool|array Contains an array with "Number", "Name", "AddInfo" Key or false on no match.
     */
    protected function _searchForFollowingNumberInAddressLine($addressLine)
    {
        return preg_match(
            '/\s*(?P<Name>[^\d]*[^\d\s])\s*((?P<Number>\d[^\s]*)\s*(?P<AddInfo>.*))*/',
            $addressLine,
            $matches
        ) ? $matches : false;
    }

    /**
     * Matches a pattern with a leading possible number (as a string) against the given address line.
     *
     * @param string $addressLine
     *
     * @return bool|array Contains an array with "Number", "Name", "AddInfo" Key or false on no match.
     */
    protected function _searchForLeadingNumberInAddressLine($addressLine)
    {
        return preg_match(
            '/\s*(?P<Number>\d[^\s]*)*\s*(?P<Name>[^\d]*[^\d\s])\s*(?P<AddInfo>.*)/',
            $addressLine,
            $matches
        ) ? $matches : false;
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
        $this->getLogger()->debug(
            'Amazon raw address',
            array('amazonAddress' => $oAmazonData)
        );

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
