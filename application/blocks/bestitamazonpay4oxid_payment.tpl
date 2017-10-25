[{if $oViewConf->getAmazonPayIsActive() && $oView->getCheckedPaymentId()=='bestitamazon' && $smarty.session.amazonOrderReferenceId}]

    [{if $oViewConf->getAmazonLoginIsActive()}]
        [{assign var="sAmazonWidgetUrl" value=$oViewConf->getAmazonProperty('sAmazonLoginWidgetUrl')}]
    [{else}]
        [{assign var="sAmazonWidgetUrl" value=$oViewConf->getAmazonProperty('sAmazonWidgetUrl')}]
    [{/if}]

    [{assign var="sAmazonSellerId" value=$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]
    [{assign var="sModuleUrl" value=$oViewConf->getModuleUrl('bestitamazonpay4oxid')}]

    [{oxscript include="`$sAmazonWidgetUrl`?sellerId=`$sAmazonSellerId`" priority=11}]
    [{oxscript include="`$sModuleUrl`out/src/js/bestitamazonpay4oxid.js" priority=11}]
    [{oxstyle  include="`$sModuleUrl`out/src/css/bestitamazonpay4oxid.css"}]

    <div class="hidden">[{$smarty.block.parent}]</div>

    <div id="walletWidgetDiv"></div>

    [{capture name="sBestitAmazonScript"}]
        $(document).ready(function () {
            $("#paymentNextStepBottom").hide();

            [{if $oViewConf->getAmazonLoginIsActive()}]
                amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');
            [{/if}]

            new OffAmazonPayments.Widgets.Wallet({
                sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                amazonOrderReferenceId:  '[{$smarty.session.amazonOrderReferenceId}]',
                design: {
                    designMode: 'responsive'
                },
                onPaymentSelect: function(orderReference) {
                    $("#paymentNextStepBottom").fadeIn();
                },
                onError: function(error) {
                    setTimeout(function(){window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=cleanAmazonPay';} , 3000);
                }
            }).bind("walletWidgetDiv");
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.sBestitAmazonScript}]

    <h3 id="paymentHeader" class="blockHead">[{ oxmultilang ident="HERE_YOU_CAN_ENETER_MESSAGE" }]</h3>
    <textarea id="orderRemark" cols="60" rows="7" name="order_remark" class="areabox" >[{$oView->getOrderRemark()}]</textarea>

    <div id="amazonPayClean" class="PaymentStep"><a href="[{$oViewConf->getSslSelfLink()}]cl=user&fnc=cleanAmazonPay">[{ oxmultilang ident="BESTITAMAZONPAY_SWITCH_BACK_2_STANDARD" }]</a></div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]