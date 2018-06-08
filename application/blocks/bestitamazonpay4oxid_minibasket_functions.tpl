[{$smarty.block.parent}]
[{if $oViewConf->getAmazonConfigValue('blShowAmazonPayButtonAtCartPopup') === true}]
    <div class="amazonPayMiniBasket">
        [{include file="bestitamazonpay4oxid_paybutton.tpl"}]
    </div>
[{/if}]