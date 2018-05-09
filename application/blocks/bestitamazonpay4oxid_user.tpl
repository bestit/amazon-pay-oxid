[{*Older Amazon Pay functionality !*}]
[{if $oViewConf->getAmazonPayIsActive() && $smarty.session.amazonOrderReferenceId && !$oViewConf->getAmazonLoginIsActive()}]

    [{capture name="sBestitAmazonAddressWidget"}]
        [{include file="bestitamazonpay4oxid_src.tpl"}]
        <div id="addressBookWidgetDiv"></div>

        [{capture name="sBestitAmazonScript"}]
            $(document).ready(function () {
                new OffAmazonPayments.Widgets.AddressBook({
                    sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                    amazonOrderReferenceId:  '[{$smarty.session.amazonOrderReferenceId}]',
                    onAddressSelect: function(orderReference) {
                        $("#amazonNextStep").fadeIn();
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onError: function(error) {
                        setTimeout(function(){window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=cleanAmazonPay';} , 3000);
                    }
                }).bind("addressBookWidgetDiv");
            });
        [{/capture}]
        [{oxscript add=$smarty.capture.sBestitAmazonScript}]

    [{/capture}]

[{*Newer Amazon Pay & Login functionality !*}]
[{elseif $oViewConf->getAmazonPayIsActive() && $smarty.session.amazonOrderReferenceId && $oViewConf->getAmazonLoginIsActive()}]

    [{capture name="sBestitAmazonAddressWidget"}]
        [{include file="bestitamazonpay4oxid_src.tpl"}]

        <div id="addressBookWidgetDiv"></div>

        [{capture name="sBestitAmazonScript"}]
            $(document).ready(function () {
                amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');

                new OffAmazonPayments.Widgets.AddressBook({
                    sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                    amazonOrderReferenceId:  '[{$smarty.session.amazonOrderReferenceId}]',
                    onAddressSelect: function(orderReference) {
                        $("#amazonNextStep").fadeIn();
                    },
                    design: {
                        designMode: 'responsive'
                    },
                    onError: function(error) {
                        setTimeout(function(){window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=cleanAmazonPay';} , 3000);
                    }
                }).bind("addressBookWidgetDiv");
            });
        [{/capture}]
        [{oxscript add=$smarty.capture.sBestitAmazonScript}]

    [{/capture}]

[{/if}]

[{if $smarty.capture.sBestitAmazonAddressWidget}]
    [{$smarty.capture.sBestitAmazonAddressWidget}]
    <div id="amazonPayClean"><a href="[{$oViewConf->getSslSelfLink()}]cl=user&fnc=cleanAmazonPay">[{oxmultilang ident="BESTITAMAZONPAY_SWITCH_BACK_2_STANDARD"}]</a></div>

    <div id="amazonNextStep" class="lineBox clear well well-sm">
        <a href="[{$oViewConf->getBasketLink()}]" class="btn btn-default pull-left previous prevStep submitButton largeButton" id="userBackStepBottom">[{oxmultilang ident="PREVIOUS_STEP"}]</a>
        <a href="[{$oViewConf->getSslSelfLink()}]cl=payment&fnc=setPrimaryAmazonUserData" class="btn btn-primary pull-right nextStep submitButton largeButton" id="userNextStepBottom">[{oxmultilang ident="CONTINUE_TO_NEXT_STEP"}]</a>
        <div class="clearfix"></div>
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]