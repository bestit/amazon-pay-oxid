[{include file="headitem.tpl" title="[OXID Benutzerverwaltung]"}]

[{if $readonly}]
    [{assign var="readonly" value="readonly disabled"}]
[{else}]
    [{assign var="readonly" value=""}]
[{/if}]

[{assign var="oConfig" value=$oViewConf->getConfig()}]

<form name="transfer" id="transfer" action="[{$oViewConf->getSelfLink()}]" method="post">
    [{$oViewConf->getHiddenSid()}]
    <input type="hidden" name="oxid" value="[{$oxid}]">
    <input type="hidden" name="cl" value="bestitAmazonPay4Oxid_main">
</form>

<table cellspacing="0" cellpadding="0" border="0" width="98%">
    <tr>
        <td valign="top" class="edittext" width="50%">
        [{if $edit}]
            <table width="200" border="0" cellspacing="0" cellpadding="0" nowrap>
            <tr><td class="edittext" valign="top">
            [{block name="admin_order_overview_billingaddress"}]
                <b>[{oxmultilang ident="GENERAL_BILLADDRESS"}]</b><br>
                <br>
                [{if $edit->oxorder__oxbillcompany->value}][{oxmultilang ident="GENERAL_COMPANY"}] [{$edit->oxorder__oxbillcompany->value}]<br>[{/if}]
                [{if $edit->oxorder__oxbilladdinfo->value}][{$edit->oxorder__oxbilladdinfo->value}]<br>[{/if}]
                [{$edit->oxorder__oxbillsal->value|oxmultilangsal}] [{$edit->oxorder__oxbillfname->value}] [{$edit->oxorder__oxbilllname->value}]<br>
                [{$edit->oxorder__oxbillstreet->value}] [{$edit->oxorder__oxbillstreetnr->value}]<br>
                [{$edit->oxorder__oxbillstateid->value}]
                [{$edit->oxorder__oxbillzip->value}] [{$edit->oxorder__oxbillcity->value}]<br>
                [{$edit->oxorder__oxbillcountry->value}]<br>
                [{if $edit->oxorder__oxbillcompany->value && $edit->oxorder__oxbillustid->value}]
                    <br>
                    [{oxmultilang ident="ORDER_OVERVIEW_VATID"}]
                    [{$edit->oxorder__oxbillustid->value}]<br>
                        [{if !$edit->oxorder__oxbillustidstatus->value}]
                            <span class="error">[{oxmultilang ident="ORDER_OVERVIEW_VATIDCHECKFAIL"}]</span><br>
                        [{/if}]
                [{/if}]
                <br>
                [{oxmultilang ident="GENERAL_EMAIL"}]: <a href="mailto:[{$edit->oxorder__oxbillemail->value}]?subject=[{$actshop}] - [{oxmultilang ident="GENERAL_ORDERNUM"}] [{$edit->oxorder__oxordernr->value}]" class="edittext"><em>[{$edit->oxorder__oxbillemail->value}]</em></a><br>
                <br>
            [{/block}]
            </td>
            [{if $edit->oxorder__oxdelstreet->value}]
            <td class="edittext" valign="top">
                [{block name="admin_order_overview_deliveryaddress"}]
                    <b>[{oxmultilang ident="GENERAL_DELIVERYADDRESS"}]:</b><br>
                    <br>
                    [{if $edit->oxorder__oxdelcompany->value}]Firma [{$edit->oxorder__oxdelcompany->value}]<br>[{/if}]
                    [{if $edit->oxorder__oxdeladdinfo->value}][{$edit->oxorder__oxdeladdinfo->value}]<br>[{/if}]
                    [{$edit->oxorder__oxdelsal->value|oxmultilangsal }] [{$edit->oxorder__oxdelfname->value}] [{$edit->oxorder__oxdellname->value}]<br>
                    [{$edit->oxorder__oxdelstreet->value}] [{$edit->oxorder__oxdelstreetnr->value}]<br>
                    [{$edit->oxorder__oxdelstateid->value}]
                    [{$edit->oxorder__oxdelzip->value}] [{$edit->oxorder__oxdelcity->value}]<br>
                    [{$edit->oxorder__oxdelcountry->value}]<br>
                    <br>
                [{/block}]
            </td>
            [{/if}]
            </tr></table>
            <b>[{oxmultilang ident="GENERAL_ITEM"}]:</b><br>
            <br>
            <table cellspacing="0" cellpadding="0" border="0">
            [{block name="admin_order_overview_items"}]
                [{foreach from=$orderArticles item=listitem}]
                <tr>
                    <td valign="top" class="edittext">[{$listitem->oxorderarticles__oxamount->value}] * </td>
                    <td valign="top" class="edittext">&nbsp;[{$listitem->oxorderarticles__oxartnum->value}]</td>
                    <td valign="top" class="edittext">&nbsp;[{$listitem->oxorderarticles__oxtitle->getRawValue()|oxtruncate:20:""|strip_tags}][{if $listitem->oxwrapping__oxname->value}]&nbsp;([{$listitem->oxwrapping__oxname->value}])&nbsp;[{/if}]</td>
                    <td valign="top" class="edittext">&nbsp;[{$listitem->oxorderarticles__oxselvariant->value}]</td>
                    <td valign="top" class="edittext">&nbsp;&nbsp;[{$listitem->getTotalBrutPriceFormated()}] [{$edit->oxorder__oxcurrency->value}]</td>
                    [{if $listitem->getPersParams()}]
                    <td valign="top" class="edittext">
                        [{foreach key=sVar from=$listitem->getPersParams() item=aParam name=persparams}]
                            &nbsp;&nbsp;,&nbsp;<em>
                                [{if $smarty.foreach.persparams.first && $smarty.foreach.persparams.last}]
                                    [{oxmultilang ident="GENERAL_LABEL"}]
                                [{else}]
                                    [{$sVar}] :
                                [{/if}]
                                [{$aParam}]
                            </em>
                        [{/foreach}]
                    </td>
                    [{/if}]
                </tr>
                [{/foreach}]
            [{/block}]
            </table>
            <br>
            [{if $edit->oxorder__oxstorno->value}]
            <span class="orderstorno">[{oxmultilang ident="ORDER_OVERVIEW_STORNO"}]</span><br><br>
            [{/if}]
            <b>[{oxmultilang ident="GENERAL_ATALL"}]: </b><br><br>
            <table border="0" cellspacing="0" cellpadding="0" id="order.info">
            [{block name="admin_order_overview_total"}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_IBRUTTO"}]</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedTotalBrutSum()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_DISCOUNT"}]&nbsp;&nbsp;</td>
                <td class="edittext" align="right"><b>- [{$edit->getFormattedDiscount()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_INETTO"}]</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedTotalNetSum()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{foreach key=iVat from=$aProductVats item=dVatPrice}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_IVAT"}] ([{$iVat}]%)</td>
                <td class="edittext" align="right"><b>[{$dVatPrice}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{/foreach}]
                [{if $edit->oxorder__oxvoucherdiscount->value}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_VOUCHERS"}]</td>
                <td class="edittext" align="right"><b>- [{$edit->getFormattedTotalVouchers()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{/if}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_DELIVERYCOST"}]&nbsp;&nbsp;</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedeliveryCost()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_PAYCOST"}]&nbsp;&nbsp;</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedPayCost()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{if $edit->oxorder__oxwrapcost->value}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_WRAPPING"}]&nbsp;[{if $wrapping}]([{$wrapping->oxwrapping__oxname->value}])[{/if}]&nbsp;</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedWrapCost()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{/if}]
                [{if $edit->oxorder__oxgiftcardcost->value}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident="GENERAL_CARD"}]&nbsp;[{if $giftCard}]([{$giftCard->oxwrapping__oxname->value}])[{/if}]&nbsp;</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedGiftCardCost()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{/if}]
                [{if $edit->oxorder__oxtsprotectid->value}]
                <tr>
                <td class="edittext" height="15">[{oxmultilang ident=ORDER_OVERVIEW_PROTECTION}]&nbsp;</td>
                <td class="edittext" align="right"><b>[{$tsprotectcosts}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
                [{/if}]
                <tr>
                <td class="edittext" height="25">[{oxmultilang ident="GENERAL_SUMTOTAL"}]&nbsp;&nbsp;</td>
                <td class="edittext" align="right"><b>[{$edit->getFormattedTotalOrderSum()}]</b></td>
                <td class="edittext">&nbsp;<b>[{if $edit->oxorder__oxcurrency->value}] [{$edit->oxorder__oxcurrency->value}] [{else}] [{$currency->name}] [{/if}]</b></td>
                </tr>
            [{/block}]
            </table>

            <br>
            <table>
            [{block name="admin_order_overview_checkout"}]
                [{if $paymentType}]
                <tr>
                    <td class="edittext">[{oxmultilang ident="ORDER_OVERVIEW_PAYMENTTYPE"}]: </td>
                    <td class="edittext"><b>[{$paymentType->oxpayments__oxdesc->value}]</b></td>
                </tr>
                [{/if}]
                <tr>
                    <td class="edittext">[{oxmultilang ident="ORDER_OVERVIEW_DELTYPE"}]: </td>
                    <td class="edittext"><b>[{$deliveryType->oxdeliveryset__oxtitle->value}]</b><br></td>
                </tr>
            [{/block}]
            </table>

            <br>
            [{if $paymentType && $paymentType->aDynValues}]
                <table cellspacing="0" cellpadding="0" border="0">
                [{block name="admin_order_overview_dynamic"}]
                    [{foreach from=$paymentType->aDynValues item=value}]
                    [{assign var="ident" value='ORDER_OVERVIEW_'|cat:$value->name}]
                    [{assign var="ident" value=$ident|oxupper}]
                    <tr>
                        <td class="edittext">
                        [{oxmultilang ident=$ident}]:&nbsp;
                        </td>
                        <td class="edittext">
                           [{$value->value}]
                        </td>
                    </tr>
                    [{/foreach}]
                [{/block}]
                </table><br>
            [{/if}]
            [{if $edit->oxorder__oxremark->value}]
            <b>[{oxmultilang ident="GENERAL_REMARK"}]</b>
            <table cellspacing="0" cellpadding="0" border="0">
                <tr>
                    <td class="edittext wrap">[{$edit->oxorder__oxremark->value}]</td>
                </tr>
            </table>
            [{/if}]
        [{/if}]
        </td>
        <td width="50%" valign="top" class="edittext">
            [{if $paymentType && ($paymentType->oxuserpayments__oxpaymentsid->value == 'bestitamazon' || $paymentType->oxuserpayments__oxpaymentsid->value == 'jagamazon')}]
                <table style="border:1px solid #A9A9A9;padding:5px;">
                    <tr><td colspan="2"><b>[{oxmultilang ident="BESTIT_AMAZONOPAYMENTSTATUS"}]:</b></td></tr>
                    <tr><td>[{oxmultilang ident="ORDER_OVERVIEW_INTSTATUS"}]:</td><td>[{$edit->oxorder__oxtransstatus->value}]</td></tr>
                    [{if $edit->oxorder__bestitamazonorderreferenceid->value}]<tr><td>[{oxmultilang ident="BESTIT_AMAZONORDERREFERENCEID"}]:</td><td>[{$edit->oxorder__bestitamazonorderreferenceid->value}]</td></tr>[{/if}]
                    [{if $edit->oxorder__bestitamazonauthorizationid->value}]<tr><td>[{oxmultilang ident="BESTIT_AMAZONAUTHORIZATIONID"}]:</td><td>[{$edit->oxorder__bestitamazonauthorizationid->value}]</td></tr>[{/if}]
                    [{if $edit->oxorder__bestitamazoncaptureid->value}]<tr><td>[{oxmultilang ident="BESTIT_AMAZONCAPTUREID"}]:</td><td>[{$edit->oxorder__bestitamazoncaptureid->value}]</td></tr>[{/if}]
                </table>

                <br/>

                 <table style="border:1px solid #A9A9A9;padding:5px;">
                    <tr><td colspan="3"><b>[{oxmultilang ident="BESTIT_AMAZON_SELECT_ACTION"}]:</b></td></tr>
                    <tr>
                        <td valign="top">
                            <div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=GetOrderReferenceDetails&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">GetOrderReferenceDetails</a></div>
                            <div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=Authorize&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">Authorize</a></div>
                            [{if $edit->oxorder__bestitamazonauthorizationid->value}]<div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=GetAuthorizationDetails&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">GetAuthorizationDetails</a></div>[{/if}]
                            [{if $edit->oxorder__bestitamazonauthorizationid->value}]<div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=Capture&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">Capture</a></div>[{/if}]
                            [{if $edit->oxorder__bestitamazoncaptureid->value}]<div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=GetCaptureDetails&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">GetCaptureDetails</a></div>[{/if}]
                        </td>
                        <td width="5%">&nbsp;</td>
                        <td valign="top">
                            [{if $edit->oxorder__bestitamazonauthorizationid->value}]<div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=CloseAuthorization&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">CloseAuthorization</a></div>[{/if}]
                            <div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=CancelOrderReference&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">CancelOrderReference</a></div>
                            <div><a href="[{$oConfig->getShopURL()}]index.php?cl=bestitamazoncron&fnc=amazonCall&operation=CloseOrderReference&oxid=[{$oxid}]&shp=[{$oConfig->getShopId()}]" target="_blank">CloseOrderReference</a></div>
                        </td>
                    </tr>
                </table>

                <br/>

                [{if $edit->oxorder__bestitamazoncaptureid->value}]
                    [{assign var="aRefunds" value=$oView->getRefunds()}]
                    <form action="[{$oViewConf->getSelfLink()}]" method="post">
                        [{$oViewConf->getHiddenSid()}]
                        <input type="hidden" name="cl" value="bestitAmazonPay4Oxid_main">
                        <input type="hidden" name="fnc" value="refundAmazonOrder">
                        <input type="hidden" name="oxid" value="[{$oxid}]">

                        <table style="border:1px solid #A9A9A9;padding:5px;">
                            <tr><td colspan="2"><b>[{oxmultilang ident="BESTIT_AMAZON_REFUNDS"}]:</b> [{if $aRefunds}](<a href="[{$oViewConf->getSelfLink()}]cl=bestitAmazonPay4Oxid_main&fnc=getRefundsStatus&oxid=[{$oxid}]">[{oxmultilang ident="BESTIT_AMAZON_REFRESH_REFUND_STATUS"}]</a>)[{/if}]</td></tr>
                            <tr>
                                <td class="edittext">
                                    [{oxmultilang ident="BESTIT_AMAZON_REFUND_AMOUNT"}]:
                                </td>
                                <td class="edittext">
                                    <input type="text" class="editinput" size="8" maxlength="10" name="fAmazonRefundAmount" value="[{if $smarty.post.fAmazonRefundAmount}][{$smarty.post.fAmazonRefundAmount}][{else}][{$edit->oxorder__oxtotalordersum->value}][{/if}]">
                                    [{$edit->oxorder__oxcurrency->value}]
                                </td>
                            </tr>
                            <tr>
                                <td class="edittext">
                                    [{oxmultilang ident="BESTIT_AMAZON_CONFIRM_REFUND"}]:
                                </td>
                                <td class="edittext">
                                    <input class="edittext" type="checkbox" name="blAmazonConfirmRefund" value="1">
                                </td>
                            </tr>
                            [{if $bestitrefunderror}]
                            <tr>
                                <td class="edittext red" style="color:red;" colspan="2">
                                    [{$bestitrefunderror}]
                                </td>
                            </tr>
                            [{/if}]
                            <tr>
                                <td class="edittext"></td>
                                <td class="edittext">
                                    <input type="submit" class="edittext" value="[{oxmultilang ident='BESTIT_AMAZON_PROCESS_REFUND'}]">
                                </td>
                            </tr>
                            [{if $aRefunds}]
                                <tr>
                                    <td colspan="2">
                                        <br/><b>[{oxmultilang ident="BESTIT_AMAZON_REFUND_STATUS"}]:</b>
                                    </td>
                                </tr>
                                [{foreach from=$aRefunds item=aRefund}]
                                    <tr>
                                        <td  valign="top">
                                            [{$aRefund.AMOUNT}] [{$edit->oxorder__oxcurrency->value}] - [{$aRefund.STATE}]
                                        </td>
                                        <td  valign="top" style="width:100px; word-wrap:break-word;">[{if $aRefund.BESTITAMAZONREFUNDID}] ([{$aRefund.BESTITAMAZONREFUNDID}])[{else}][{$aRefund.ERROR|replace:'. ':'.<br/> '}][{/if}]</td>
                                    </tr>
                                [{/foreach}]
                            [{/if}]
                        </table>
                    </form>
                [{/if}]

            [{else}]
                <div style="color:red;">[{oxmultilang ident="BESTIT_AMAZON_SELECTED_PAYMENT_NOT_AMAZON"}]</div>
            [{/if}]
        </td>
    </tr>
</table>

[{include file="bottomnaviitem.tpl"}]
</table>
[{include file="bottomitem.tpl"}]
