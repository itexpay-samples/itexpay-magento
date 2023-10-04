define(
        [
            "jquery",
            'mage/url',
            "Magento_Checkout/js/view/payment/default",
            "Magento_Checkout/js/action/place-order",
            "Magento_Checkout/js/model/payment/additional-validators",
            "Magento_Checkout/js/model/quote",
            "Magento_Checkout/js/model/full-screen-loader",
            "Magento_Checkout/js/action/redirect-on-success",
        ],
        function (
                $,
                mageUrl,
                Component,
                placeOrderAction,
                additionalValidators,
                quote,
                fullScreenLoader,
                redirectOnSuccessAction
                ) {
            'use strict';

            return Component.extend({
                defaults: {
                    template: 'Itex_ItexPay/payment/itex_itexpay'
                },

                redirectAfterPlaceOrder: false,

                isActive: function () {
                    return true;
                },

                /**
                 * Provide redirect to page
                 */
                redirectToCustomAction: function (url) {
                    fullScreenLoader.startLoader();
                    window.location.replace(mageUrl.build(url));
                },

                /**
                 * @override
                 */
                afterPlaceOrder: function () {

                    var checkoutConfig = window.checkoutConfig;
                    var paymentData = quote.billingAddress();
                    var itexpayConfiguration = checkoutConfig.payment.itex_itexpay;

                    if (itexpayConfiguration.integration_type == 'standard') {
                        this.redirectToCustomAction(itexpayConfiguration.integration_type_standard_url);
                    } else {
                        if (checkoutConfig.isCustomerLoggedIn) {
                            var customerData = checkoutConfig.customerData;
                            paymentData.email = customerData.email;
                        } else {
                            paymentData.email = quote.guestEmail;
                        }

                        var quoteId = checkoutConfig.quoteItemData[0].quote_id;

                        var _this = this;
                        _this.isPlaceOrderActionAllowed(false);
                        var handler = ItexPayPop.setup({
                            key: itexpayConfiguration.public_key,
                            email: paymentData.email,
                            amount: Math.ceil(quote.totals().grand_total * 100), // get order total from quote for an accurate... quote
                            phone: paymentData.telephone,
                            currency: checkoutConfig.totalsData.quote_currency_code,
                            metadata: {
                                quoteId: quoteId,
                                custom_fields: [
                                    {
                                        display_name: "QuoteId",
                                        variable_name: "quote id",
                                        value: quoteId
                                    },
                                    {
                                        display_name: "Address",
                                        variable_name: "address",
                                        value: paymentData.street[0] + ", " + paymentData.street[1]
                                    },
                                    {
                                        display_name: "Postal Code",
                                        variable_name: "postal_code",
                                        value: paymentData.postcode
                                    },
                                    {
                                        display_name: "City",
                                        variable_name: "city",
                                        value: paymentData.city + ", " + paymentData.countryId
                                    },
                                    {
                                        display_name: "Plugin",
                                        variable_name: "plugin",
                                        value: "magento-2"
                                    }
                                ]
                            },
                            callback: function (response) {
                                fullScreenLoader.startLoader();
                                $.ajax({
                                    method: "GET",
                                    url: itexpayConfiguration.api_url + "/itexpay/verify/" + response.reference + "_-~-_" + quoteId
                                }).success(function (data) {
                                    data = JSON.parse(data);
                                    //JS PSTK-logger
                                    $.ajax({
                                        method: 'POST',
                                        url: "https://itexpay.com/api/pay",
                                        data:{
                                            plugin_name: 'magento-2',
                                            transaction_reference: response.reference,
                                            public_key: itexpayConfiguration.public_key
                                        }
                                    })
                                    if (data.status) {
                                        if (data.data.status === "success") {
                                            // redirect to success page after
                                            redirectOnSuccessAction.execute();
                                            return;
                                        }
                                    }

                                    fullScreenLoader.stopLoader();

                                    _this.isPlaceOrderActionAllowed(true);
                                    _this.messageContainer.addErrorMessage({
                                        message: "Error, please try again"
                                    });
                                });
                            },
                            onClose: function(){
                                _this.redirectToCustomAction(itexpayConfiguration.recreate_quote_url);
                            }
                        });
                        handler.openIframe();
                    }
                },

            });
        }
);
