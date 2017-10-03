define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            if (window.rejoinerMarketing !== undefined) {
                var shippingAddress = quote.shippingAddress();
                if (shippingAddress['extension_attributes'] === undefined) {
                    shippingAddress['extension_attributes'] = {};
                }

                shippingAddress['extension_attributes']['rejoiner_subscribe'] = window.rejoinerMarketing.subscribe_guest_checkout;
                // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            }

            return originalAction();
        });
    };
});