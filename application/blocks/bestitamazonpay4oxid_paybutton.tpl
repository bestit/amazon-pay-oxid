[{$smarty.block.parent}]

[{capture name="sBestitAmazonPayButton"}]

    [{*Older Amazon Pay functionality !*}]
    [{if $oViewConf->getAmazonPayIsActive() && !$oViewConf->getAmazonLoginIsActive() && !$smarty.session.amazonOrderReferenceId}]
        [{assign var="sAmazonWidgetUrl" value=$oViewConf->getAmazonProperty('sAmazonWidgetUrl')}]
        [{assign var="sAmazonSellerId" value=$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]
        [{assign var="sModuleUrl" value=$oViewConf->getModuleUrl('bestitamazonpay4oxid')}]

        [{oxscript include="`$sAmazonWidgetUrl`?sellerId=`$sAmazonSellerId`" priority=11}]
        [{oxscript include="`$sModuleUrl`out/src/js/bestitamazonpay4oxid.js" priority=11}]
        [{oxstyle  include="`$sModuleUrl`out/src/css/bestitamazonpay4oxid.css"}]

        <div id="payWithAmazonDiv">
            <img src="[{$oViewConf->getAmazonProperty('sAmazonButtonUrl')}]?sellerId=[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]&size=x-large" title="[{ oxmultilang ident='BESTITAMAZONPAY_PAY_WITH_AMAZON_BUTTON'}]"/>
        </div>

        [{capture name="sBestitAmazonScript"}]
            $(document).ready(function () {
                var amazonOrderReferenceId;
                new OffAmazonPayments.Widgets.Button ({
                    sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                    onSignIn: function(orderReference) {
                        amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
                        window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&amazonOrderReferenceId=' + amazonOrderReferenceId;
                    },
                    onError: function(error) {
                        window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=basket&amazonOrderReferenceId=' + amazonOrderReferenceId;
                    }
                }).bind("payWithAmazonDiv");
            });
        [{/capture}]
        [{oxscript add=$smarty.capture.sBestitAmazonScript}]
    [{/if}]


    [{*Newer Amazon Pay & Login functionality !*}]
    [{if $oViewConf->showAmazonPayButton()}]
        [{assign var="sAmazonWidgetUrl" value=$oViewConf->getAmazonProperty('sAmazonLoginWidgetUrl')}]
        [{assign var="sAmazonSellerId" value=$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]
        [{assign var="sModuleUrl" value=$oViewConf->getModuleUrl('bestitamazonpay4oxid')}]

        [{oxscript include="`$sAmazonWidgetUrl`?sellerId=`$sAmazonSellerId`" priority=11}]
        [{oxscript include="`$sModuleUrl`out/src/js/bestitamazonpay4oxid.js" priority=11}]
        [{oxstyle  include="`$sModuleUrl`out/src/css/bestitamazonpay4oxid.css"}]

        <div id="payWithAmazonDiv"></div>

        [{capture name="sBestitAmazonLoginScript"}]
            $(document).ready(function () {
                amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');

                [{assign var="aButtonStyle" value="-"|explode:$oViewConf->getAmazonConfigValue('sAmazonPayButtonStyle')}]

                var authRequest;
                OffAmazonPayments.Button('payWithAmazonDiv', '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]', {
                    type: '[{$aButtonStyle.0}]',
                    size: ($('meta[name=apple-mobile-web-app-capable]').attr("content")=='yes') ? 'medium' : 'small',
                    color: '[{$aButtonStyle.1}]',
                    language: '[{$oViewConf->getAmazonLanguage()}]',
                    authorization: function() {
                        loginOptions =  {scope: 'profile payments:widget payments:shipping_address', popup: true};
                        authRequest = amazon.Login.authorize(loginOptions, function(response) {
                            addressConsentToken = response.access_token;
                        });
                    },
                    onSignIn: function(orderReference) {
                        amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
                        window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=amazonLogin&redirectCl=user&amazonOrderReferenceId=' + amazonOrderReferenceId + '&access_token=' + addressConsentToken;
                    },
                    onError: function(error) {
                        setTimeout(function(){window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=cleanAmazonPay';} , 3000);
                    }
                });
            });
        [{/capture}]
        [{oxscript add=$smarty.capture.sBestitAmazonLoginScript}]
    [{/if}]

[{/capture}]

[{if $smarty.capture.sBestitAmazonPayButton|trim}]
    [{if $oViewConf->getActiveClassName()=='user'}]
        <div id="amazonPayButtonLine" class="lineBox">
            [{$smarty.capture.sBestitAmazonPayButton}]
            <h3>[{ oxmultilang ident="BESTITAMAZONPAYLOGIN_PURCHASE_WITH_AMAZON" }]</h3>
            <div style="clear:both;"></div>
        </div>
    [{else}]
        [{$smarty.capture.sBestitAmazonPayButton}]
    [{/if}]
[{/if}]