define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/checkout-data'
], function ($, wrapper, quote, customer, checkoutData) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            if (window.rejoinerMarketing !== undefined) {
                var shippingAddress = quote.shippingAddress(),
                    customerEmail = '';
                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }

                shippingAddress['extension_attributes']['rejoiner_subscribe'] = window.rejoinerMarketing.subscribe_guest_checkout;

                if (customer.isLoggedIn()) {
                    customerEmail = customer.customerData.email;
                } else {
                    customerEmail = checkoutData.getValidatedEmailValue;
                }

                if (customerEmail) {
                    shippingAddress['extension_attributes']['rejoiner_email'] = customerEmail;
                }
                // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            }

            return originalAction();
        });
    };
});