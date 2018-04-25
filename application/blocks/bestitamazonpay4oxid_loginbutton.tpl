[{$smarty.block.parent}]

[{if $oViewConf->showAmazonLoginButton()}]
    [{include file="bestitamazonpay4oxid_src.tpl"}]
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
                    loginOptions =  {scope: 'profile payments:widget payments:shipping_address payments:billing_address', popup: true};
                    authRequest = amazon.Login.authorize (loginOptions, '[{$oViewConf->getSslSelfLink()|html_entity_decode}]fnc=amazonLogin');
                },
                onError: function(error) {

                }
            });
        });
    [{/capture}]

    [{oxscript add=$smarty.capture.sBestitAmazonLoginScript}]

    <div id="amazonLoginButton" style="display: none;">
        <div class="amazonTooltip">
            <i>?</i>
            <div class="amazonTooltipContent">[{oxmultilang ident="BESTITAMAZONPAY_LOGIN_BUTTON_HINT"}]</div>
        </div>
    </div>
[{/if}]