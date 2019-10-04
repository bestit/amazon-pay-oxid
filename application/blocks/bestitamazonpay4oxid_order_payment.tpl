[{assign var="oPayment" value=$oView->getPayment()}]
[{if $oViewConf->getAmazonPayIsActive() && $oPayment->getId()=='bestitamazon' && $smarty.session.amazonOrderReferenceId}]
    [{include file="bestitamazonpay4oxid_src.tpl"}]

    [{oxscript include="`$sAmazonWidgetUrl`?sellerId=`$sAmazonSellerId`" priority=11}]
    [{oxstyle  include="`$sModuleUrl`out/src/css/bestitamazonpay4oxid.css"}]

    <div id="orderPayment">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <h3 class="section">
                <strong>[{oxmultilang ident="PAYMENT_METHOD"}]</strong>
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="payment">
                <input type="hidden" name="fnc" value="">
                <button type="submit" class="submitButton largeButton">[{oxmultilang ident="EDIT"}]</button>
            </h3>
        </form>

        [{if $smarty.get.action=='changePayment'}]
            <span class="status error corners">[{oxmultilang ident="BESTITAMAZONPAY_PAYMENT_INVALID"}]</span><br/><br/>
        [{/if}]

        <div id="readOnlyWalletWidgetDiv"></div>
        [{if $smarty.get.action=='changePayment'}]
            [{capture name="sBestitAmazonScript"}]
                $(document).ready(function () {
                    $("#orderConfirmAgbBottom button[type=submit]").hide();
                    [{if $oViewConf->getAmazonLoginIsActive()}]
                        amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');
                    [{/if}]
                    new OffAmazonPayments.Widgets.Wallet({
                        sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                        amazonOrderReferenceId: '[{$smarty.session.amazonOrderReferenceId}]',
                        [{if $oViewConf->getAmazonConfigValue('blBestitAmazonPay4OxidEnableMultiCurrency')}]
                            presentmentCurrency: '[{$oViewConf->getBasketCurrency()}]',
                        [{/if}]
                        design: {
                            designMode: 'responsive'
                        },
                        onPaymentSelect: function(orderReference) {
                            $("#orderConfirmAgbBottom button[type=submit]").fadeIn();
                        },
                        onError: function(error) {
                        }
                    }).bind("readOnlyWalletWidgetDiv");
                });
            [{/capture}]
            <div id="amazonPayClean" class="PaymentStep"><a href="[{$oViewConf->getSslSelfLink()}]cl=user&fnc=cleanAmazonPay">[{oxmultilang ident="BESTITAMAZONPAY_SWITCH_BACK_2_STANDARD"}]</a></div>
        [{else}]
            [{capture name="sBestitAmazonScript"}]
                $(document).ready(function () {
                    [{if $oViewConf->getAmazonLoginIsActive()}]
                        amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');
                    [{/if}]
                    new OffAmazonPayments.Widgets.Wallet({
                        sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                        amazonOrderReferenceId: '[{$smarty.session.amazonOrderReferenceId}]',
                        [{if $oViewConf->getAmazonConfigValue('blBestitAmazonPay4OxidEnableMultiCurrency')}]
                            presentmentCurrency: '[{$oViewConf->getBasketCurrency()}]',
                        [{/if}]
                        displayMode: "Read",
                        design: {
                            designMode: 'responsive'
                        },
                        onError: function(error) {
                            setTimeout(function(){window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=cleanAmazonPay&bestitAmazonPay4OxidErrorCode='+error.getErrorCode()+'&error='+error.getErrorMessage();} , 3000);
                        }
                    }).bind("readOnlyWalletWidgetDiv");
                });
            [{/capture}]
        [{/if}]

        [{oxscript add=$smarty.capture.sBestitAmazonScript}]

        [{if $oView->getOrderRemark()}]
            <br/>
            <dl class="orderRemarks">
                <dt>[{oxmultilang ident="WHAT_I_WANTED_TO_SAY"}]</dt>
                <dd>
                    [{$oView->getOrderRemark()}]
                </dd>
            </dl>
        [{/if}]
    </div>

    <div id="orderShipping">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <h3 class="section">
                <strong>[{oxmultilang ident="SHIPPING_CARRIER"}]</strong>
                [{$oViewConf->getHiddenSid()}]
                <input type="hidden" name="cl" value="payment">
                <input type="hidden" name="fnc" value="">
                <button type="submit" class="submitButton largeButton">[{oxmultilang ident="EDIT"}]</button>
            </h3>
        </form>
        [{assign var="oShipSet" value=$oView->getShipSet()}]
        [{$oShipSet->oxdeliveryset__oxtitle->value}]
    </div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]