[{if $oViewConf->getAmazonPayIsActive()
    && $oPayment->getId()=='bestitamazon'
    && $smarty.session.amazonOrderReferenceId
    && !$oViewConf->getBasketHash()
}]
    [{capture name="sBestitAmazonPlaceOrderScript"}]
        var modalLoading = {
            init : function(start) {
                var _this = this;
                if (start) {
                    _this.construct();
                    window.location.href = "#openModalLoading";
                }
            },
            construct : function() {
                var _this = this;
                var html = '<div id="openModalLoading" class="modalDialog"><div><div class="loading-spinner"></div></div></div>';

                _this.appendHtml(document.body, html);
                _this.appendCss();
            },
            appendHtml : function(el, str) {
                var div = document.createElement('div');
                div.innerHTML = str;
                while (div.children.length > 0) {
                    el.appendChild(div.children[0]);
                }
            },
            appendCss : function() {
                var css = '.modalDialog {position: fixed;font-family: Arial, Helvetica, sans-serif;top: 0;right: 0;bottom: 0;left: 0; background: rgba(0, 0, 0, 0.8);z-index: 99999;opacity:0; -webkit-transition: opacity 400ms ease-in; -moz-transition: opacity 400ms ease-in;transition: opacity 400ms ease-in; pointer-events: none;}  .modalDialog:target {opacity:1;pointer-events: auto;}  .modalDialog > div {width: 100%;position: relative;margin: 20% auto;}@-webkit-keyframes rotate-forever { 0% { -webkit-transform: rotate(0deg); -moz-transform: rotate(0deg); -ms-transform: rotate(0deg); -o-transform: rotate(0deg); transform: rotate(0deg); } 100% { -webkit-transform: rotate(360deg); -moz-transform: rotate(360deg); -ms-transform: rotate(360deg); -o-transform: rotate(360deg); transform: rotate(360deg); } } @-moz-keyframes rotate-forever { 0% { -webkit-transform: rotate(0deg); -moz-transform: rotate(0deg); -ms-transform: rotate(0deg); -o-transform: rotate(0deg); transform: rotate(0deg); } 100% { -webkit-transform: rotate(360deg); -moz-transform: rotate(360deg); -ms-transform: rotate(360deg); -o-transform: rotate(360deg); transform: rotate(360deg); } } @keyframes rotate-forever { 0% { -webkit-transform: rotate(0deg); -moz-transform: rotate(0deg); -ms-transform: rotate(0deg); -o-transform: rotate(0deg); transform: rotate(0deg); } 100% { -webkit-transform: rotate(360deg); -moz-transform: rotate(360deg); -ms-transform: rotate(360deg); -o-transform: rotate(360deg); transform: rotate(360deg); } } .loading-spinner { -webkit-animation-duration: 0.75s; -moz-animation-duration: 0.75s; animation-duration: 0.75s; -webkit-animation-iteration-count: infinite; -moz-animation-iteration-count: infinite; animation-iteration-count: infinite; -webkit-animation-name: rotate-forever; -moz-animation-name: rotate-forever; animation-name: rotate-forever; -webkit-animation-timing-function: linear; -moz-animation-timing-function: linear; animation-timing-function: linear; height: 30px; width: 30px; border: 8px solid #ffffff; border-right-color: transparent; border-radius: 50%; display: inline-block; }.loading-spinner { position: absolute; top: 50%; right: 0; bottom: 0; left: 50%; margin: -15px 0 -15px;}',
                    head = document.head || document.getElementsByTagName('head')[0],
                    style = document.createElement('style');

                style.type = 'text/css';

                if (style.styleSheet){
                    style.styleSheet.cssText = css;
                } else {
                    style.appendChild(document.createTextNode(css));
                }

                head.appendChild(style);
            }
        };

        $(document).ready(function () {
            var $content = $('#content');
            var $forms = $('form');

            $forms.each(function() {
                var $form = $(this);
                var $classInput = $('input[name="cl"]', $form);
                var $functionInput = $('input[name="fnc"]', $form);
                var $agbCheckbox = $('input[name="ord_agb"][type="checkbox"]');

                if ($classInput.val() === 'order'
                    && $functionInput.val() === '[{$oView->getExecuteFnc()}]'
                ) {
                    $form.on('submit', function(e) {
                        var agbCheckboxChecked = $agbCheckbox.length >= 1 && $agbCheckbox.is(':checked');
                        var noAgbCheckbox = $agbCheckbox.length === 0;

                        if (noAgbCheckbox || agbCheckboxChecked) {
                            e.preventDefault();
                            modalLoading.init(true);
                            OffAmazonPayments.initConfirmationFlow(
                                '[{$oViewConf->getAmazonConfigValue('sAmazonSellerId')}]',
                                '[{$smarty.session.amazonOrderReferenceId}]',
                                function (confirmationFlow) {
                                    $.ajax({
                                        url: "/index.php",
                                        data: {
                                            cl: "order",
                                            fnc: "confirmAmazonOrderReference",
                                            stoken: "[{$oViewConf->getSessionToken()}]",
                                            formData: $('#' + $form[0].id).serialize()
                                        },
                                        success: function (data) {
                                            if (data.success === true) {
                                                confirmationFlow.success();
                                            } else {
                                                window.location = data.redirectUrl;
                                            }
                                        },
                                        error: function (data) {
                                            confirmationFlow.error();
                                            window.location = '/index.php?cl=user&fnc=cleanAmazonPay';
                                        },
                                        timeout: 5000
                                    });
                                }
                            );
                        }
                    });
                }
            });
        });
    [{/capture}]
    [{oxscript add=$smarty.capture.sBestitAmazonPlaceOrderScript}]
[{/if}]
[{$smarty.block.parent}]
