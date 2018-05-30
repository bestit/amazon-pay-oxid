[{$smarty.block.parent}]
[{if $oViewConf->getAmazonConfigValue('blShowAmazonPayButtonAtDetails') === true}]
    [{include file="bestitamazonpay4oxid_paybutton.tpl" addToCart=true blCanBuy=$blCanBuy}]
[{/if}]
