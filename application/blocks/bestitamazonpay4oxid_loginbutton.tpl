[{$smarty.block.parent}]

[{if $oViewConf->showAmazonLoginButton()}]
    [{assign var="sAmazonWidgetUrl" value=$oViewConf->getAmazonProperty('sAmazonLoginWidgetUrl')}]
    [{assign var="sAmazonSellerId" value=$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]
    [{assign var="sModuleUrl" value=$oViewConf->getModuleUrl('bestitamazonpay4oxid')}]

    [{oxscript include="`$sAmazonWidgetUrl`?sellerId=`$sAmazonSellerId`" priority=11}]
    [{oxscript include="`$sModuleUrl`out/src/js/bestitamazonpay4oxid.js" priority=11}]
    [{oxstyle  include="`$sModuleUrl`out/src/css/bestitamazonpay4oxid.css"}]

    <div id="amazonLoginButton" style="display: none;"></div>

    [{capture name="sBestitAmazonLoginScript"}]
        $(document).ready(function () {
            amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');

            [{assign var="aButtonStyle" value="-"|explode:$oViewConf->getAmazonConfigValue('sAmazonLoginButtonStyle')}]

            var authRequest;
            OffAmazonPayments.Button('amazonLoginButton', '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]', {
                type: '[{$aButtonStyle.0}]',
                size: ($('meta[name=apple-mobile-web-app-capable]').attr("content")=='yes') ? 'medium' : 'small',
                color: '[{$aButtonStyle.1}]',
                language: '[{$oViewConf->getAmazonLanguage()}]',
                authorization: function() {
                    loginOptions =  {scope: 'profile payments:widget payments:shipping_address', popup: true};
                    authRequest = amazon.Login.authorize (loginOptions, '[{$oViewConf->getSslSelfLink()|html_entity_decode}]fnc=amazonLogin');
                },
                onError: function(error) {

                }
            });
        });
    [{/capture}]

    [{oxscript add=$smarty.capture.sBestitAmazonLoginScript}]
[{/if}]