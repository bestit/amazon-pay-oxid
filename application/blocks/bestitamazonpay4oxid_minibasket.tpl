[{$smarty.block.parent}]
[{if $_prefix === 'modal'
     && $oViewConf->getAmazonConfigValue('blShowAmazonPayButtonInBasketFlyout') === true
}]
    <div id="amazonPayBasketModalButton" style="display:none;">
        [{include file="bestitamazonpay4oxid_paybutton.tpl"}]
    </div>
[{/if}]