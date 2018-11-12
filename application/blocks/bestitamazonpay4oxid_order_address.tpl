[{assign var="oPayment" value=$oView->getPayment()}]
[{if $oViewConf->getAmazonPayIsActive() && $oPayment->getId()=='bestitamazon' && $smarty.session.amazonOrderReferenceId}]
    [{include file="bestitamazonpay4oxid_src.tpl"}]

    [{assign var="aAmazonBillingAddress" value=$oView->getAmazonBillingAddress()}]
    [{if $aAmazonBillingAddress}]
        <div class="amazonBillingAddress panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">[{oxmultilang ident="BESTITAMAZONPAY_BILLING_ADDRESS"}]</h3>
            </div>
            [{assign var="oUser" value=$oView->getUser()}]
            <div class="col-xs-12 col-md-6">
                <h4>[{oxmultilang ident="BESTITAMAZONPAY_BILLING_ADDRESS_CURRENT"}]</h4>
                [{$oUser->oxuser__oxfname->value}]<br>
                [{$oUser->oxuser__oxlname->value}]<br>
                [{$oUser->oxuser__oxstreet->value}] [{$oUser->oxuser__oxstreetnr->value}]<br>
                [{$oUser->oxuser__oxzip->value}] [{$oUser->oxuser__oxcity->value}]<br>
                [{assign var="sCountryNameUser" value=$oView->getCountryName($oUser->oxuser__oxcountryid->value)}]
                [{if $sCountryNameUser !== ''}]
                    [{$sCountryNameUser}]<br>
                [{/if}]
            </div>
            <div class="col-xs-12 col-md-6">
                <h4>[{oxmultilang ident="BESTITAMAZONPAY_BILLING_ADDRESS_NEW"}]</h4>
                [{$aAmazonBillingAddress.oxfname}]<br>
                [{$aAmazonBillingAddress.oxlname}]<br>
                [{$aAmazonBillingAddress.oxstreet}] [{$aAmazonBillingAddress.oxstreetnr}]<br>
                [{$aAmazonBillingAddress.oxzip}] [{$aAmazonBillingAddress.oxcity}]<br>
                [{assign var="sCountryName" value=$oView->getCountryName($aAmazonBillingAddress.oxcountryid)}]
                [{if $sCountryName !== ''}]
                    [{$sCountryName}]<br>
                [{/if}]
            </div>
            <div class="panel-body">
                <p>
                    <a href="[{oxgetseourl ident=$oViewConf->getSelfLink()|cat:"cl=order" params="fnc=updateUserWithAmazonData"}]"
                       class="btn btn-default submitButton largeButton"
                    >
                        [{oxmultilang ident="BESTITAMAZONPAY_UPDATE_BILLING_ADDRESS"}]
                    </a>
                </p>
            </div>
        </div>
    [{/if}]

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