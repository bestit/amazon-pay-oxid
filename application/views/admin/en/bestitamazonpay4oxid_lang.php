<?php

/**
 * Backend language entries for EN
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */

$sLangName  = "English";

$aLang = array(
    'charset'                                               => 'UTF-8',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidSettings'       => 'API Settings',
    'SHOP_MODULE_blAmazonSandboxActive'                     => 'Sandbox mode',
    'SHOP_MODULE_sAmazonSellerId'                           => 'Seller (Merchant) ID',
    'SHOP_MODULE_sAmazonAWSAccessKeyId'                     => 'Amazon MWS key',
    'SHOP_MODULE_sAmazonSignature'                          => 'MWS Secret Key',
    'SHOP_MODULE_blAmazonLogging'                           => 'Logging enabled',
    'SHOP_MODULE_blAmazonLoggingLevel'                     => 'Log Level',
    'SHOP_MODULE_blAmazonLoggingLevel_debug'               => 'Debug',
    'SHOP_MODULE_blAmazonLoggingLevel_error'               => 'Error',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidLoginSettings'        => 'Amazon Login Settings',
    'SHOP_MODULE_blAmazonLoginActive'                             => 'Amazon Login active',
    'SHOP_MODULE_sAmazonLoginClientId'                            => 'Client ID',
    'SHOP_MODULE_sAmazonLoginButtonStyle'                         => 'Login Button style',
    'SHOP_MODULE_sAmazonPayButtonStyle'                           => 'Pay Button style',
    'SHOP_MODULE_sAmazonLoginButtonStyle_LwA-LightGray'           => 'Login with Amazon (light gray)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_LwA-Gold'                => 'Login with Amazon (gold)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_LwA-DarkGray'            => 'Login with Amazon (dark gray)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_Login-LightGray'         => 'Login (light gray)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_Login-Gold'              => 'Login (gold)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_Login-DarkGray'          => 'Login (dark gray)',
    'SHOP_MODULE_sAmazonPayButtonStyle_PwA-LightGray'             => 'Pay with Amazon (light gray)',
    'SHOP_MODULE_sAmazonPayButtonStyle_PwA-Gold'                  => 'Pay with Amazon (gold)',
    'SHOP_MODULE_sAmazonPayButtonStyle_PwA-DarkGray'              => 'Pay with Amazon (dark gray)',
    'SHOP_MODULE_sAmazonPayButtonStyle_Pay-LightGray'             => 'Pay (light gray)',
    'SHOP_MODULE_sAmazonPayButtonStyle_Pay-Gold'                  => 'Pay (gold)',
    'SHOP_MODULE_sAmazonPayButtonStyle_Pay-DarkGray'              => 'Pay (dark gray)',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidLocalization'   => 'Merchant Account Localization',
    'SHOP_MODULE_sAmazonLocale'                             => 'Country of Merchant Account Registration',
    'SHOP_MODULE_sAmazonLocale_DE'                          => 'DE',
    'SHOP_MODULE_sAmazonLocale_UK'                          => 'UK',
    'SHOP_MODULE_sAmazonLocale_US'                          => 'US',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidConfiguration'   => 'Configuration',
    'SHOP_MODULE_sAmazonMode'                               => 'Authorize Mode',
    'SHOP_MODULE_sAmazonMode_BASIC_FLOW'                    => 'Basic Flow',
    'SHOP_MODULE_sAmazonMode_OPTIMIZED_FLOW'                => 'Optimized Flow',
    'SHOP_MODULE_sAmazonAuthorize'                          => 'Status updates',
    'SHOP_MODULE_sAmazonCapture'                            => 'Capture handling',
    'SHOP_MODULE_blAmazonERP'                               => 'ERP Mode',
    'SHOP_MODULE_sAmazonERPModeStatus'                      => 'ERP Mode Order Status',
    'SHOP_MODULE_sAmazonAuthorize_IPN'                      => 'Receive status updates via IPN',
    'SHOP_MODULE_sAmazonAuthorize_CRON'                     => 'Poll status updates via Cron job',
    'SHOP_MODULE_sAmazonCapture_DIRECT'                     => 'Direct capture after Authorize',
    'SHOP_MODULE_sAmazonCapture_SHIPPED'                    => 'Capture after order has been marked as shipped',

    'SHOP_MODULE_sSandboxSimulation'                                                 => 'Sandbox Simulation mode',
    'SHOP_MODULE_sSandboxSimulation_'                                                => '(none)',
    'SHOP_MODULE_sSandboxSimulation_SetOrderReferenceDetailsPaymentMethodNotAllowed' => 'SetOrderReferenceDetails : PaymentMethodNotAllowed',
    'SHOP_MODULE_sSandboxSimulation_CloseOrderReferenceAmazonClosed'                 => 'CloseOrderReference : AmazonClosed',
    'SHOP_MODULE_sSandboxSimulation_AuthorizeInvalidPaymentMethod'                   => 'Authorize : InvalidPaymentMethod',
    'SHOP_MODULE_sSandboxSimulation_AuthorizeAmazonRejected'                         => 'Authorize : AmazonRejected',
    'SHOP_MODULE_sSandboxSimulation_AuthorizeTransactionTimedOut'                    => 'Authorize : TransactionTimedOut',
    'SHOP_MODULE_sSandboxSimulation_AuthorizeExpiredUnused'                          => 'Authorize : ExpiredUnused',
    'SHOP_MODULE_sSandboxSimulation_AuthorizeAmazonClosed'                           => 'Authorize : AmazonClosed',
    'SHOP_MODULE_sSandboxSimulation_CapturePending'                                  => 'Capture : Pending',
    'SHOP_MODULE_sSandboxSimulation_CaptureAmazonRejected'                           => 'Capture : AmazonRejected',
    'SHOP_MODULE_sSandboxSimulation_CaptureAmazonClosed'                             => 'Capture : AmazonClosed',
    'SHOP_MODULE_sSandboxSimulation_RefundAmazonRejected'                            => 'Refund : AmazonRejected',
    'SHOP_MODULE_blShowAmazonPayButtonInBasketFlyout'                                => 'Show Amazon Pay Button at basket flyout',
    'SHOP_MODULE_blBestitAmazonPay4OxidEnableMultiCurrency'                          => 'Activate multi-currency functionality',
    'SHOP_MODULE_blShowAmazonPayButtonAtDetails'                                     => 'Show Amazon Pay button on the details page',
    'SHOP_MODULE_blShowAmazonPayButtonAtCartPopup'                                   => 'Show Amazon Pay button on the cart popup',
    'SHOP_MODULE_aAmazonReverseOrderCountries'                                       => 'ISO2 code of the countries where the AddressLineX returned from Amazon are reversed (AddressLine1 == company, AddressLine2 == street)',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidLanguages'  => 'Language Settings',
    'SHOP_MODULE_aAmazonLanguages'                      => "Language mapping ('Oxid language abbreviation' => 'Amazon language value')",

    'tbclorder_bestitamazonpay'                          => 'Amazon Pay',
    'BESTIT_AMAZON_SELECTED_PAYMENT_NOT_AMAZON'               => 'Order has different payment method than Amazon Pay. No further action can be done within this order',
    'BESTIT_AMAZON_SELECT_ACTION'                             => 'Pro options: Select action with the order',
    'BESTIT_AMAZONOPAYMENTSTATUS'                             => 'Amazon Pay Status',
    'BESTIT_AMAZON_REFUNDS'                                   => 'Refunds',
    'BESTIT_AMAZON_REFUND_AMOUNT'                             => 'Refund Sum',
    'BESTIT_AMAZON_CONFIRM_REFUND'                            => 'Confirm Refund',
    'BESTIT_AMAZON_PROCESS_REFUND'                            => 'Process New Refund',
    'BESTIT_AMAZONORDERREFERENCEID'                           => 'Amazon Order Reference ID',
    'BESTIT_AMAZONAUTHORIZATIONID'                            => 'Amazon Authorization ID',
    'BESTIT_AMAZONCAPTUREID'                                  => 'Amazon Capture ID',
    'BESTIT_AMAZONREFUNDID'                                   => 'Amazon RefundID',
    'BESTIT_AMAZON_REFUND_STATUS'                             => 'Amazon Refund Status',

    'BESTIT_AMAZON_REFRESH_REFUND_STATUS'                     => 'Refresh refund status',
    'BESTITAMAZONPAY_ORDER_NO'                           => 'Order no.',
    'BESTIT_AMAZON_SANDBOX_SIMULATION_ACTIONS'                => 'Sandbox Simulations',

    'BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX'       => 'Please check the Confirm Refund checkbox to perform the refund',
    'BESTITAMAZONPAY_INVALID_REFUND_AMOUNT'              => 'Invalid refund amount, please correct and try again',

    'BESTIT_AMAZON_QUICK_CONFIG' => 'Quick configuration'
);