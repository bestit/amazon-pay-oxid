<?php

/**
 * Backend language entries for DE
 *
 * @author best it GmbH & Co. KG <info@bestit-online.de>
 */

$sLangName  = "Deutsch";

$aLang = array(
    'charset'                                              => 'UTF-8',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidSettings'   => 'API-Einstellungen',
    'SHOP_MODULE_blAmazonSandboxActive'                    => 'Sandbox-Modus',
    'SHOP_MODULE_sAmazonSellerId'                          => 'Händler-ID (Händlernummer)',
    'SHOP_MODULE_sAmazonAWSAccessKeyId'                    => 'Amazon MWS-Zugangsschlüssel',
    'SHOP_MODULE_sAmazonSignature'                         => 'MWS geheimer Schlüssel',
    'SHOP_MODULE_blAmazonLogging'                          => 'Logs speichern',
    'SHOP_MODULE_blAmazonLoggingLevel'                     => 'Log Level',
    'SHOP_MODULE_blAmazonLoggingLevel_debug'               => 'Debug',
    'SHOP_MODULE_blAmazonLoggingLevel_error'               => 'Error',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidLoginSettings'     => 'Amazon Login-Einstellungen',
    'SHOP_MODULE_blAmazonLoginActive'                             => 'Amazon Login aktiv',
    'SHOP_MODULE_sAmazonLoginClientId'                            => 'Client-ID',
    'SHOP_MODULE_sAmazonLoginButtonStyle'                         => 'Login-Button Design',
    'SHOP_MODULE_sAmazonPayButtonStyle'                           => 'Bezahlen-Button Design',
    'SHOP_MODULE_sAmazonLoginButtonStyle_LwA-LightGray'           => 'Login mit Amazon (hellgrau)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_LwA-Gold'                => 'Login mit Amazon (gold)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_LwA-DarkGray'            => 'Login mit Amazon (dunkelgrau)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_Login-LightGray'         => 'Login (hellgray)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_Login-Gold'              => 'Login (gold)',
    'SHOP_MODULE_sAmazonLoginButtonStyle_Login-DarkGray'          => 'Login (dunkelgray)',
    'SHOP_MODULE_sAmazonPayButtonStyle_PwA-LightGray'             => 'Bezahlen mit Amazon (hellgrau)',
    'SHOP_MODULE_sAmazonPayButtonStyle_PwA-Gold'                  => 'Bezahlen mit Amazon (gold)',
    'SHOP_MODULE_sAmazonPayButtonStyle_PwA-DarkGray'              => 'Bezahlen mit Amazon (dunkelgrau)',
    'SHOP_MODULE_sAmazonPayButtonStyle_Pay-LightGray'             => 'Bezahlen (hellgrau)',
    'SHOP_MODULE_sAmazonPayButtonStyle_Pay-Gold'                  => 'Bezahlen (gold)',
    'SHOP_MODULE_sAmazonPayButtonStyle_Pay-DarkGray'              => 'Bezahlen (dunkelgrau)',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidLocalization'  => 'Händlerkonto-Lokalisierung',
    'SHOP_MODULE_sAmazonLocale'                            => 'Land des Händlerkontos',
    'SHOP_MODULE_sAmazonLocale_DE'                         => 'DE',
    'SHOP_MODULE_sAmazonLocale_UK'                         => 'UK',
    'SHOP_MODULE_sAmazonLocale_US'                         => 'US',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidConfiguration'  => 'Konfiguration',
    'SHOP_MODULE_sAmazonMode'                              => 'Autorisierungsmodus',
    'SHOP_MODULE_sAmazonMode_BASIC_FLOW'                   => 'Basic Flow',
    'SHOP_MODULE_sAmazonMode_OPTIMIZED_FLOW'               => 'Optimierter Flow',
    'SHOP_MODULE_sAmazonAuthorize'                         => 'Statusupdates',
    'SHOP_MODULE_sAmazonCapture'                           => 'Art des Captures',
    'SHOP_MODULE_blAmazonERP'                              => 'ERP-Modus',
    'SHOP_MODULE_sAmazonERPModeStatus'                     => 'Bestellstatus für ERP-Modus',
    'SHOP_MODULE_sAmazonAuthorize_IPN'                     => 'Statusupdates via IPN empfangen',
    'SHOP_MODULE_sAmazonAuthorize_CRON'                    => 'Statusupdates via cron job abfragen',
    'SHOP_MODULE_sAmazonCapture_DIRECT'                    => 'Direktes Capture nach Autorisierung',
    'SHOP_MODULE_sAmazonCapture_SHIPPED'                   => 'Capture nachdem Bestellung als Versand markiert wurde',

    'SHOP_MODULE_sSandboxSimulation'                                                 => 'Auswahl der Sandbox-Simulation',
    'SHOP_MODULE_sSandboxSimulation_'                                                => '(keine)',
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
    'SHOP_MODULE_blShowAmazonPayButtonInBasketFlyout'                                => 'Zeige Amazon Pay Button im Basket Flyout',
    'SHOP_MODULE_blBestitAmazonPay4OxidEnableMultiCurrency'                          => 'Fremdwährungen aktivieren',
    'SHOP_MODULE_blShowAmazonPayButtonAtDetails'                                     => 'Amazon Pay-Button auf Produktdetailseite anzeigen',
    'SHOP_MODULE_blShowAmazonPayButtonAtCartPopup'                                   => 'Amazon Pay-Button im Warenkorb Popup anzeigen',
    'SHOP_MODULE_aAmazonReverseOrderCountries'                                       => 'ISO2 Code der Länder, bei denen die AddressLineX Rückgaben von Amazon vertauscht sind (AddressLine1 == Firma, AddressLine2 == Straße)',

    'SHOP_MODULE_GROUP_bestitAmazonPay4OxidLanguages'  => 'Sprach Einstellungen',
    'SHOP_MODULE_aAmazonLanguages'                      => "Sprach Einstellungen ('Oxid Sprachk&uuml;rzel' => 'Amazon Sprachwert')",

    'tbclorder_bestitamazonpay'                          => 'Amazon Pay',
    'BESTIT_AMAZON_SELECTED_PAYMENT_NOT_AMAZON'               => 'Keine Amazon Pay-Bestellung. Keine weiteren Aktionen möglich.',
    'BESTIT_AMAZON_SELECT_ACTION'                             => 'Profi-Optionen: Wählen Sie eine andere Aktion',
    'BESTIT_AMAZONOPAYMENTSTATUS'                             => 'Amazon Pay Status',
    'BESTIT_AMAZON_REFUNDS'                                   => 'Erstattung',
    'BESTIT_AMAZON_REFUND_AMOUNT'                             => 'Summe Erstattung',
    'BESTIT_AMAZON_CONFIRM_REFUND'                            => 'Erstattung bestätigen',
    'BESTIT_AMAZON_PROCESS_REFUND'                            => 'Erstattung durchführen',
    'BESTIT_AMAZONORDERREFERENCEID'                           => 'Amazon Order Reference ID',
    'BESTIT_AMAZONAUTHORIZATIONID'                            => 'Amazon Authorization ID',
    'BESTIT_AMAZONCAPTUREID'                                  => 'Amazon Capture ID',
    'BESTIT_AMAZONREFUNDID'                                   => 'Amazon Refund ID',
    'BESTIT_AMAZON_REFUND_STATUS'                             => 'Amazon Refund Status',

    'BESTIT_AMAZON_REFRESH_REFUND_STATUS'                     => 'Statusanzeige refreshen',
    'BESTITAMAZONPAY_ORDER_NO'                           => "Bestellnr.",
    'BESTIT_AMAZON_SANDBOX_SIMULATION_ACTIONS'                => 'Sandbox-Simulationen',

    'BESTITAMAZONPAY_PLEASE_CHECK_REFUND_CHECKBOX'       => 'Bitte aktivieren Sie die “Erstattung bestätigen”-Checkbox, um die Erstattung durchzuführen.',
    'BESTITAMAZONPAY_INVALID_REFUND_AMOUNT'              => 'Ungültiger Rückerstattungsbetrag, bitte korrigieren und erneut versuchen',

    'BESTIT_AMAZON_QUICK_CONFIG' => 'Schnell Konfiguration'
);