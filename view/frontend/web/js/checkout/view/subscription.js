/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Customer/js/model/customer',
    'uiRegistry'
], function ($, Component, ko, customer, uiRegistry) {
    'use strict';

    return Component.extend({
        defaults: {
            shouldObserveParentEmail: 0
        },

        showSubscriptionLogin: ko.observable(false),
        showSubscriptionGuest: ko.observable(false),

        initialize: function() {
            this._super();

            if (window.rejoinerMarketing) {

                if (window.rejoinerMarketing.show_on_login_checkout) {
                    this.showSubscriptionLogin(true);
                }

                if (!customer.isLoggedIn() && window.rejoinerMarketing.show_on_guest_checkout && this.shouldObserveParentEmail) {
                    this.showSubscriptionGuest(true);

                    // Prevent displaying 2 subscribe checkboxes
                    if (window.rejoinerMarketing.show_on_login_checkout) {
                        var email = uiRegistry.get((uiRegistry.get(this.parentName)).parentName),
                            that = this;

                        email.isPasswordVisible.subscribe(function (newValue) {
                            that.toggleSubscribeGuestForm(newValue);
                        });
                        this.toggleSubscribeGuestForm(email.isPasswordVisible());
                    }
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
        },

        toggleSubscribeGuestForm: function(newValue) {
            if (window.rejoinerMarketing.show_on_guest_checkout) {
                   if (newValue) {
                    this.showSubscriptionGuest(false);
                } else {
                    this.showSubscriptionGuest(true);
                }
            }

        }
    });
});
