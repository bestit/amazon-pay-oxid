ALTER TABLE `oxorder` CHANGE `JAGAMAZONORDERREFERENCEID` `BESTITAMAZONORDERREFERENCEID` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `oxorder` CHANGE `JAGAMAZONAUTHORIZATIONID` `BESTITAMAZONAUTHORIZATIONID` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `oxorder` CHANGE `JAGAMAZONCAPTUREID` `BESTITAMAZONCAPTUREID` VARCHAR( 32 ) NOT NULL;
ALTER TABLE `oxuser` CHANGE `JAGAMAZONID` `BESTITAMAZONID` VARCHAR( 50 ) NOT NULL;
RENAME TABLE `jagamazonrefunds` TO `bestitamazonrefunds`;
ALTER TABLE `bestitamazonrefunds` CHANGE `JAGAMAZONREFUNDID` `BESTITAMAZONREFUNDID` VARCHAR( 32 ) NOT NULL;

UPDATE `oxobject2payment` SET `OXPAYMENTID` = 'bestitamazon'
WHERE `OXPAYMENTID` = 'jagamazon';

UPDATE `oxobject2group` SET `OXOBJECTID` = 'bestitamazon'
WHERE `OXOBJECTID` = 'jagamazon';

UPDATE `oxconfig` SET `OXMODULE` = 'module:bestitamazonpay4oxid'
WHERE `OXMODULE` = 'module:jagamazonpayment4oxid';


UPDATE `oxpayments` SET `OXID` = 'bestitamazon'
WHERE `OXID` = 'jagamazon';

UPDATE `oxpayments` SET `OXDESC` = 'Amazon Pay', `OXDESC` = 'Amazon Pay'
WHERE `OXID` = 'bestitamazon';

UPDATE `oxcontents` SET `OXID` = 'bestitAmazonInvalidPaymentEmail', `OXLOADID` = 'bestitAmazonInvalidPaymentEmail'
WHERE `OXID` = 'jagamazonInvalidPaymentEmail';

UPDATE `oxcontents` SET `OXID` = 'bestitAmazonRejectedPaymentEmail', `OXLOADID` = 'bestitAmazonRejectedPaymentEmail'
WHERE `OXID` = 'jagamazonRejectedPaymentEmail';

UPDATE `oxcontents` SET `OXCONTENT` = REPLACE(`OXCONTENT`, 'JAGAMAZONPAYMENTS_', 'BESTITAMAZONPAY_')
WHERE `OXCONTENT` like '%JAGAMAZONPAYMENTS_%';

CREATE TABLE IF NOT EXISTS `bestitamazonobject2reference` (
  `OXOBJECTID` VARCHAR( 32 ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `AMAZONORDERREFERENCEID` VARCHAR( 32 ) NOT NULL,
  PRIMARY KEY(`OXOBJECTID`, `AMAZONORDERREFERENCEID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `bestitamazonobject2reference` (`OXOBJECTID`, `AMAZONORDERREFERENCEID`)
  (SELECT `OXID`, `OXLNAME` FROM `oxuser` WHERE `OXLNAME` LIKE 'S02-%');

INSERT INTO `bestitamazonobject2reference` (`OXOBJECTID`, `AMAZONORDERREFERENCEID`)
  (SELECT `OXID`, `OXLNAME` FROM `oxaddress` WHERE `OXLNAME` LIKE 'S02-%');