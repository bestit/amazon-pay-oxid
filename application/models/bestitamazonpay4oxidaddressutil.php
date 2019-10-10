<?php

/**
 * Model to handle address parsing
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */
class bestitAmazonPay4OxidAddressUtil extends bestitAmazonPay4OxidContainer
{
    /**
     * Filters the pattern result and adds default values for the required fields.
     *
     * @param array $matches
     *
     * @return array
     */
    private function _cleanPatternSearchResult(array $matches)
    {
        $requiredFields = array('Street', 'StreetNr', 'AddInfo');
        $requiredValues = array();

        foreach ($requiredFields as $requiredField) {
            $requiredValues[$requiredField] = isset($matches[$requiredField]) ? $matches[$requiredField] : '';
        }

        return $requiredValues;
    }

    /**
     * Saves the street + house number in the street field and remove the company field, if number is saved in company.
     *
     * Amazon had a short period, in which their address form was wrong so the street number must be handled specially.
     *
     * @param array $possibleStreetLines
     *
     * @return array Return the changed to original street lines.
     */
    private function _fixBrokenHouseNumberDataIfNeeded(array $possibleStreetLines)
    {
        if ($this->_lineContainsJustAHouseNumber($possibleStreetLines['usual company'])) {
            $possibleStreetLines['usual street'] = $possibleStreetLines['usual street'] . ' ' .
                $possibleStreetLines['usual company'];

            $possibleStreetLines['usual company'] = '';
        }

        return $possibleStreetLines;
    }

    /**
     * Checks the address lines and returns the matching steet and company information.
     *
     * @param stdClass $amazonData
     *
     * @return array The first value is the company and second value is the street line.
     */
    protected function _getCompanyAndStreetLineFromAmazonData(stdClass $amazonData)
    {
        $possibleStreetLines = $this->_getPossibleStreetLinesInTheConfiguredOrder($amazonData);

        do {
            // Search the street.
            $street = (string) array_shift($possibleStreetLines);
        } while (!$street && $possibleStreetLines);

        // Use the possible remainder as the company.
        $company = ($possibleCompanyLines = array_filter($possibleStreetLines))
            ? implode(', ', $possibleCompanyLines)
            : '';

        return array($company, $street);
    }

    /**
     * Returns the possible streets fields from the amazon data relative to the configured order for the spec. country
     *
     * @param stdClass $amazonData
     *
     * @return array
     */
    private function _getPossibleStreetLinesInTheConfiguredOrder(stdClass $amazonData)
    {
        $possibleStreetLines = array(
            // If line 1 is filled it becomes the street usually and the second line the company.
            // If line 1 is empty, the street falls back to line 2.
            'usual street' => is_string($amazonData->AddressLine1) ? trim($amazonData->AddressLine1) : '',
            'usual company' => is_string($amazonData->AddressLine2) ? trim($amazonData->AddressLine2) : '',
        );

        $countriesWithCompanyOnTop = $this->getConfig()->getConfigParam('aAmazonReverseOrderCountries');
        $countryIsosAsKeys = array_flip($countriesWithCompanyOnTop);

        if (isset($countryIsosAsKeys[$amazonData->CountryCode])) {
            // Usually line 2 is the street but if line 2 empty, line 1 becomes the street, so move line 2 to the top.
            $possibleStreetLines = array_reverse($possibleStreetLines);

            $possibleStreetLines = $this->_fixBrokenHouseNumberDataIfNeeded($possibleStreetLines);
        }

        // Line 3 is the company or additional company infos, everytime! But if the other lines are empty, it can be
        // become the fallback street as well.
        $possibleStreetLines['definitive company info'] = is_string($amazonData->AddressLine3)
            ? trim($amazonData->AddressLine3)
            : '';

        return $possibleStreetLines;
    }

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
     * Does it seem that the given address line just contains a house number?
     *
     * @param string $addressLine
     *
     * @return bool
     */
    private function _lineContainsJustAHouseNumber($addressLine)
    {
        return (bool) preg_match('/^\s*(?P<StreetNr>\d+[\s\w]{0,9})\s*$/', $addressLine);
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

        $relevantParsing = $this->_cleanPatternSearchResult($relevantParsing);

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
     * @param stdClass $oAmazonData
     *
     * @return array The parsed address result.
     */
    protected function _parseAddressFields($oAmazonData)
    {
        list($sCompany, $sStreet) = $this->_getCompanyAndStreetLineFromAmazonData($oAmazonData);

        $result = array('AddInfo' => '', 'CompanyName' => $sCompany, 'Street' => '', 'StreetNr' => '');

        if ($sStreet) {
            $result = array_merge($result, $this->_parseSingleAddress($sStreet, $oAmazonData->CountryCode));
        }

        $this->getLogger()->debug(
            'Amazon address parsed',
            array('result' => $result, 'amazonAddress' => $oAmazonData)
        );

        return $result;
    }

    /**
     * Matches a pattern with a following possible number (as a string) against the given address line.
     *
     * @param string $addressLine
     *
     * @return bool|array Contains an array with "Street", "StreetNr", "AddInfo" Key or false on no match.
     */
    protected function _searchForFollowingNumberInAddressLine($addressLine)
    {
        return preg_match(
            '/\s*(?P<Street>[^\d]*[^\d\s])\s*((?P<StreetNr>\d[^\s]*)\s*(?P<AddInfo>.*))*/',
            $addressLine,
            $matches
        ) ? $matches : false;
    }

    /**
     * Matches a pattern with a leading possible number (as a string) against the given address line.
     *
     * @param string $addressLine
     *
     * @return bool|array Contains an array with "StreetNr", "Street", "AddInfo" Key or false on no match.
     */
    protected function _searchForLeadingNumberInAddressLine($addressLine)
    {
        return preg_match(
            '/\s*(?P<StreetNr>\d[^\s]*)*\s*(?P<Street>[^\d]*[^\d\s])\s*(?P<AddInfo>.*)/',
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

        $aResult['CountryId'] = $this->getDatabase()->getOne($sSql);

        $aResult = array_merge($aResult, $this->_parseAddressFields($oAmazonData));

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
