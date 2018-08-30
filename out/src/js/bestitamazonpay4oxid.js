// Workaround for oxid bug 6675 (https://bugs.oxid-esales.com/view.php?id=6675)
if (typeof Carousel === 'undefined') {
  var Carousel = function () {};
  Carousel.prototype.init = function () {};
}

if (typeof amazonPayNextStepButtonId === 'undefined') {
    var amazonPayNextStepButtonId = '#paymentNextStepBottom';
}

if (typeof amazonPayHandleLoginButton === 'undefined') {
    var amazonPayHandleLoginButton = true;
}

if (typeof amazonPayCustomLoginButtonAddMap === 'undefined') {
    var amazonPayCustomLoginButtonAddMap = {};
}

$(document).ready(function() {
    //If we have payment selection radios - we are in payment page, display/hide Pay with amazon button
    var $inputPayment = $('input[name=paymentid]');

    if ($inputPayment.length) {
        //Change Next button to Amazon if Amazon pay is pre-selected
        var paymentid = $("input[name=paymentid]:checked").val();
        var $payWithAmazonDiv = $("#payWithAmazonDiv");

        if (paymentid === 'bestitamazon') {
            $payWithAmazonDiv.show();
            $(amazonPayNextStepButtonId).hide();
        }

        //Payment change event
        $inputPayment.change(function() {
            if (this.value === 'bestitamazon') {
                $payWithAmazonDiv.show();
                $(amazonPayNextStepButtonId).hide();
            }
            else {
                $payWithAmazonDiv.hide();
                $(amazonPayNextStepButtonId).show();
            }
        });

        //Mobile only
        if (isMobile() === true) {
            //Payment change event for mobile version
            $('#payment').find('.dropdown-menu li').click(function() {
                if ($('a', this).data("selection-id") === 'bestitamazon'){
                    $payWithAmazonDiv.show();
                    $(amazonPayNextStepButtonId).hide();
                } else {
                    $payWithAmazonDiv.hide();
                    $(amazonPayNextStepButtonId).show();
                }
            });

            //Hide normal Payment selection if we have walletWidgetDiv in Payment page
            if ($('#walletWidgetDiv').length) {
                $("#paymentMethods").hide();
            }
        }
    }

    var $amazonBasketModalButton = $('#amazonPayBasketModalButton');

    if ($amazonBasketModalButton.length) {
        $('.basketFlyout .modal-footer .btn.btn-default').before($amazonBasketModalButton);
        $amazonBasketModalButton.show();
    }

    //If Amazon Login button exists try to place it to proper place in the page
    var $amazonLoginButton = $('#amazonLoginButton');

    if (amazonPayHandleLoginButton === true && $amazonLoginButton.length) {
        var blDontShowAmazonButton = true;

        //Mobile version
        if (isMobile() === true) {
            //Login page
            if ($('form[name=login]').length){
                $amazonLoginButton.detach().appendTo('form[name=login] ul.form');
                blDontShowAmazonButton = false;
            }
        } else { //Desktop version
            var loginButtonAddMap = {
                // Flow
                '.cl-account': { //Add Amazon login button to my account page
                    'appendTo': 'form[name="login"]'
                },
                '.cl-register': { //Add Amazon login button to register page
                    'appendTo': '#openAccHeader'
                },
                '.service-menu-box #loginBox': { //Add Amazon login button to the header login box
                    'appendTo': '.service-menu-box #loginBox'
                },
                // Azure
                '.checkoutCollumns': {
                    'prependTo': '.checkoutCollumns ul:eq(0)',
                    'cssClass' : 'amazonLoginButtonInsideCheckout2'
                },
                '#optionLogin': { //Add Amazon login button to my account page
                    'appendTo': '#optionLogin',
                    'cssClass' : 'amazonLoginButtonInsideCheckout1'
                },
                '#loginAccount': { //Add Amazon login to checkout address step with 3 options
                    'appendTo': '#content .col:eq(1)',
                    'cssClass' : 'amazonLoginButtonInsideLoginPage'
                },
                '#openAccHeader': { //Add Amazon login button to register page
                    'prependTo': '#content .form:eq(0)',
                    'cssClass' : 'amazonLoginButtonInsideRegisterPage'
                },
                '#loginBox': { //Add Amazon login button to the header login box
                    'appendTo': '#loginBox .loginForm',
                    'cssClass' : 'amazonLoginButtonInsideHeaderBox'
                }
            };

            jQuery.extend(loginButtonAddMap, amazonPayCustomLoginButtonAddMap);

            for (var existingObject in loginButtonAddMap){
                if (loginButtonAddMap.hasOwnProperty(existingObject)) {
                    if ($(existingObject).length) {
                        var currentEntry = loginButtonAddMap[existingObject];
                        var action = null;
                        var actionClass = null;
                        
                        if (currentEntry.hasOwnProperty('appendTo')) {
                            action = 'appendTo';
                            actionClass = currentEntry.appendTo;
                        } else if (currentEntry.hasOwnProperty('prependTo')) {
                            action = 'prependTo';
                            actionClass = currentEntry.prependTo;
                        }

                        blDontShowAmazonButton = false;
                        
                        if (action !== null && $(actionClass).length) {
                            (function (action, actionClass, currentEntry, $button) {
                                var waitForButton = setInterval(function () {
                                    $button.hide();

                                    if ($button.html() !== '') {
                                        $button.detach()[action](actionClass);

                                        if (currentEntry.hasOwnProperty('cssClass')) {
                                            $(actionClass).addClass(currentEntry.cssClass);
                                        }

                                        $button.show();
                                        clearInterval(waitForButton);
                                    }
                                }, 1000);
                            })(action, actionClass, currentEntry, $amazonLoginButton);

                            break;
                        }
                    }
                }
            }
        }

        //Show button when the page is loaded, if we have found a position
        if (blDontShowAmazonButton === false) {
            $amazonLoginButton.show();
        } else {
            $amazonLoginButton.hide();
        }
    } else if ($amazonLoginButton.length) {
        $amazonLoginButton.show();
    }


    //Prevent to click Order button twice, after click show loader.gif image instead of the button
    var $deliveryAddress = $("input[name='sDeliveryAddressMD5']");

    if ($deliveryAddress.length > 0) {
        var orderForm = $deliveryAddress.closest("form");

        $(orderForm).submit(function(event) {
            var submitButton = $('[type="submit"]', orderForm);
            $(submitButton).css("display", "none");
            $(submitButton).after('<span class="amazonLoadingImage"></span>');

            // Check if session is still alive and we still have user logged in
            // Check for div with id "readOnlyWalletWidgetDiv" because this will only be available on amazon orders
            // see modules/bestit/amazonpay4oxid/application/blocks/bestitamazonpay4oxid_order_payment.tpl
            if ($('#readOnlyWalletWidgetDiv').length > 0 && typeof amazon !== 'undefined') {
                var options = {
                    scope: 'payments:widget',
                    popup: true,
                    interactive: 'never'
                };

                amazon.Login.authorize(options, function(response) {
                    if (response.error) {
                      window.location = 'index.php?cl=user&fnc=cleanAmazonPay';
                    }
                });
            }
        });
    }


    //Function returns if we are in mobile version or regular
    function isMobile()
    {
        if ($('meta[name=apple-mobile-web-app-capable]').attr("content") === 'yes') {
            $('body').attr("id", "amazonMobile");
            return true;
        }

        return false;
    }

    //Check If it's mobile version of shop
    isMobile();
});