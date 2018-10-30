# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec2.0.0.html).

## [Unreleased]
### Added
- This changelog file with documentation of changes since version 3.1.0

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

[Unreleased]: https://github.com/bestit/amazon-pay-oxid/compare/3.2.1...HEAD
[3.2.1]: https://github.com/bestit/amazon-pay-oxid/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.4...3.2.0
[3.1.4]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.3...3.1.4
[3.1.3]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/bestit/amazon-pay-oxid/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/bestit/amazon-pay-oxid/compare/3.0.2...3.1.0