# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](https://semver.org/spec2.0.0.html).

## [3.6.2] - 2019-10-30
### Fixed
- Add missing parent::__construct calls [#124](https://github.com/bestit/amazon-pay-oxid/pull/124)
- Make testFinalizeOrder more robust [#124](https://github.com/bestit/amazon-pay-oxid/pull/124)

## [3.6.1] - 2019-10-25
### Fixed
- Fixed the missing parent call in "OxidEsales\Eshop\Core\Email" [#120](https://github.com/bestit/amazon-pay-oxid/pull/120)

## [3.6.0] - 2019-10-10
### Fixed
- The ajax request for confirmation is now fired, if agb click is not active or checked [#115](https://github.com/bestit/amazon-pay-oxid/pull/115)

### Changed
- You don't need to configure the special street contries (aAmazonStreetNoStreetCountries) anymore. 
The module just checks the matches and chooses which field is used for the street. 
Refactored a bit! [#116](https://github.com/bestit/amazon-pay-oxid/pull/116)

### Added
- Added a special number handling for the error cases, in which amazon saves just the house number in address line 2. [#117](https://github.com/bestit/amazon-pay-oxid/pull/117) 

## [3.5.0] - 2019-10-04
### Changed
- Refactored Docker setup
- Changed the success link and added real umlauts
- Inserted real numbers for the position in the metadata
- Added oxNew for the custom logger

## [3.4.0] - 2019-08-09
### Added
- Added local development setup
- Added structure for an module logging
- Implemented rudimentary logging for all module functions

## [3.3.1] - 2019-05-24
### Fixed
- Fix isActive check for basket sums lower than 1 ([#92](https://github.com/bestit/amazon-pay-oxid/issues/92))
- Fix typo ([#76](https://github.com/bestit/amazon-pay-oxid/pull/76))
- Fix if rendering button if article variant is fetched via ajax in article detail page ([#91](https://github.com/bestit/amazon-pay-oxid/pull/91))

## [3.3.0] - 2019-05-13
### Added
- Addresses without street numbers will now get parsed, too ([OXAP-155](https://bestit.atlassian.net/browse/OXAP-155))
  - Make sure to set up required fields correctly to allow street without streetnumber e.g. remove oxuser__oxstreetnr and oxaddress__oxstreetnr from "Mandatory fields in User Registration Form" in OXID Backend -> Master Settings -> Core Settings -> Tab: Settings -> Other Settings
- Add PSD2 support ([OXAP-187](https://bestit.atlassian.net/browse/OXAP-187), [OXAP-192](https://bestit.atlassian.net/browse/OXAP-192), [OXAP-199](https://bestit.atlassian.net/browse/OXAP-199), [OXAP-200](https://bestit.atlassian.net/browse/OXAP-200), [OXAP-201](https://bestit.atlassian.net/browse/OXAP-201), [OXAP-202](https://bestit.atlassian.net/browse/OXAP-202))
### Changed
- Changed License to the MIT License ([OXAP-172](https://bestit.atlassian.net/browse/OXAP-172))
- Changed OXID module settings parameter to correct wording 'constraints' instead of 'constrains' ([OXAP-181](https://bestit.atlassian.net/browse/OXAP-181))
- Update Amazon Pay SDK to version 3.4.1 ([OXAP-190](https://bestit.atlassian.net/browse/OXAP-190))
### Fixed
- Fixed possibility that there will be orders created with ...@amazon.com email addresses and no billing address under certain circumstances ([OXAP-183](https://bestit.atlassian.net/browse/OXAP-183))
### Removed
- Removed settings for customization of amazon locale settings ([OXAP-119](https://bestit.atlassian.net/browse/OXAP-119))

## [3.2.2] - 2018-11-21
### Added
- This changelog file with documentation of changes since version 3.1.0

### Fixed
- Button "Use Amazon billing address" now copies street and streetnumber ([OXAP-147](https://bestit.atlassian.net/browse/OXAP-147))
- Fixed Amazon Pay Button without active option "Login With Amazon" ([OXAP-148](https://bestit.atlassian.net/browse/OXAP-148))
- Display Amazon Pay actions on admin for old paymemt id (jagamazon) ([OXAP-161](https://bestit.atlassian.net/browse/OXAP-161))

## [3.2.1] - 2018-10-16
### Fixed
- Fixed unit tests to work in different phpunit versions

## [3.2.0] - 2018-10-11
### Added
- Added address parsing for countries that have another order of street and street number

### Changed
- Update oxorder__oxtransstatus if order gets closed
- Table bestitamazonrefunds will now be changed from update from JAG version to bestit version, too

### Fixed
- Corrected id of div for amazon pay button to let form be serialized without errors
- Corrected JS on cl=order to prevent errors on orders not paid with Amazon Pay

## [3.1.4] - 2018-07-16
### Fixed
- Removed deprecated typehint on oxOrder::_parentFinalizeOrder()

## [3.1.3] - 2018-07-03
### Fixed
- Fixed empty "change payment" link

## [3.1.2] - 2018-06-15
### Added
- Added module setting for visibility of Amazon Pay button on details page
- Added module setting for visibility of Amazon Pay button on minibasket

### Fixed
- Fixed broken build for latest OXID 6
- Fixed bug with CurrencyMismatch error

## [3.1.1] - 2018-05-17
### Added
- Added module setting to enable multi currency not only as hidden feature

## [3.1.0] - 2018-05-15
### Added
- Added tooltip for Amazon Pay and Amazon Login button
- Orders will now get closed by cronjob
- Quick checkout for single product from details page with Amazon Pay
- Check for save on module settings if leaving page without saving
- Send plugin version on request to Amazon Pay API
- Amazon Pay button on minibasket

### Changed
- Made Unit Tests compatible with OXID testing library v4.0
- Changed license from GPL-3.0 to GPL-3.0-only
- Moved Amazon Pay button in front of parent template output
- Close order after it gets captured

### Fixed
- Line endings of files bestitamazonpay4oxidipnhandler.php and bestitamazonpay4oxidloginclient.php
- Module settings will now be saved even if you deactivate and activate the module again

[3.3.1]: https://github.com/bestit/amazon-pay-oxid/compare/3.3.0...3.3.1
[3.3.0]: https://github.com/bestit/amazon-pay-oxid/compare/3.2.2...3.3.0
[3.2.2]: https://github.com/bestit/amazon-pay-oxid/compare/3.2.1...3.2.2
[3.2.1]: https://github.com/bestit/amazon-pay-oxid/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.4...3.2.0
[3.1.4]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.3...3.1.4
[3.1.3]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/bestit/amazon-pay-oxid/compare/3.0.2...3.1.0