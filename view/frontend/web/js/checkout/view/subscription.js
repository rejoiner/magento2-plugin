/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer'
], function ($, Component, ko, customer) {
    'use strict';

    return Component.extend({
        showSubscriptionLogin: ko.observable(false),
        showSubscriptionGuest: ko.observable(false),

        initialize: function() {
            this._super();

            if (window.rejoinerMarketing) {
                if (window.rejoinerMarketing.show_on_login_checkout) {
                    this.showSubscriptionLogin(true);
                }

                if (!customer.isLoggedIn() && window.rejoinerMarketing.show_on_guest_checkout) {
                    this.showSubscriptionGuest(true);
                }
            }

            return this;
        },

        shouldBeChecked: function() {
            return Boolean(window.rejoinerMarketing.checked_by_default);
        },

        getLabel: function() {
            return window.rejoinerMarketing.label;
        },

        updateSubscribe: function(input, event) {
            window.rejoinerMarketing.subscribe_guest_checkout = $(event.target).is(':checked') ? 1 : 0;
            return true;
        }
    });
});