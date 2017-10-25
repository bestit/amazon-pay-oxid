[{assign var="oPayment" value=$oView->getPayment()}]
[{if $oViewConf->getAmazonPayIsActive() && $oPayment->getId()=='bestitamazon' && $smarty.session.amazonOrderReferenceId}]

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

    <div id="orderAddress">
        <form action="[{$oViewConf->getSslSelfLink()}]" method="post">
            <h3 class="section">
            <strong>[{oxmultilang ident="ADDRESSES"}]</strong>
            [{$oViewConf->getHiddenSid()}]
            <input type="hidden" name="cl" value="user">
            <input type="hidden" name="fnc" value="">
            <button type="submit" class="submitButton largeButton">[{oxmultilang ident="EDIT"}]</button>
            </h3>
        </form>
        <dl>
            <div id="readOnlyAddressBookWidgetDiv"></div>
            [{capture name="sBestitAmazonScript"}]
                $(document).ready(function () {
                    [{if $oViewConf->getAmazonLoginIsActive()}]
                        amazon.Login.setClientId('[{$oViewConf->getAmazonConfigValue('sAmazonLoginClientId')}]');
                    [{/if}]
                    new OffAmazonPayments.Widgets.AddressBook({
                        sellerId: '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                        amazonOrderReferenceId:  '[{$smarty.session.amazonOrderReferenceId}]',
                        displayMode: "Read",
                        design: {
                            designMode: 'responsive'
                        },
                        onError: function(error) {
                            setTimeout(function(){window.location = '[{$oViewConf->getSslSelfLink()|html_entity_decode}]cl=user&fnc=cleanAmazonPay';} , 3000);
                        }
                    }).bind("readOnlyAddressBookWidgetDiv");
                });
            [{/capture}]
            [{oxscript add=$smarty.capture.sBestitAmazonScript}]
        </dl>
    </div>
    <div style="clear:both;"></div>
[{else}]
    [{$smarty.block.parent}]
[{/if}]