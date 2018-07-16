/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'

], function($, customerData) {
    'use strict';

    $.widget('rejoiner.acrTracking', {
        skipSubscription: false,

        options: {
            rejoinerSiteId: '',
            rejoinerDomain: '',
            outputConversionData: false
        },

        _create: function() {
            var storageData = customerData.get('rejoiner-acr'),
                that = this;

            if (this.options.rejoinerSiteId && this.options.rejoinerDomain) {
                window._rejoiner = that.getRejoinerObject();
            }

            storageData.subscribe(function () {
                if (!that.skipSubscription) {
                    window._rejoiner = that.getRejoinerObject(true);
                }
            });

            this.connectRemoteScript();
        },

        getRejoinerObject: function (isAjaxUpdate) {
            var isAjaxUpdate = isAjaxUpdate || false,
                _rejoiner = window._rejoiner || [],
                storageData = customerData.get('rejoiner-acr')();

            if (this.options.trackCartDataOnThisPage == 1 || isAjaxUpdate) {
                if (storageData.cartData) {
                    _rejoiner.push(["setCartData", JSON.parse(storageData.cartData)]);
                }
                if (storageData.cartItems) {
                    JSON.parse(storageData.cartItems).forEach(function (element) {
                        _rejoiner.push(["setCartItem", element]);
                    });
                }

                if (storageData.removedItems) {
                    JSON.parse(storageData.removedItems).forEach(function (element) {
                        _rejoiner.push(["removeCartItem", {product_id: element}]);
                    });
                    this.skipSubscription = true;
                    delete storageData.removedItems;
                    customerData.set('rejoiner-acr', storageData);
                    this.skipSubscription = false;
                }
            }

            if (!isAjaxUpdate) {
                if (this.options.rejoinerSiteId) {
                    _rejoiner.push(["setAccount", this.options.rejoinerSiteId]);
                }
                if (this.options.rejoinerDomain) {
                    _rejoiner.push(["setDomain", this.options.rejoinerDomain]);
                }
                if (this.options.trackNumberEnabled) {
                    _rejoiner.push(["trackNumbers"]);
                }
                if (this.options.persistFormsEnabled) {
                    _rejoiner.push(["persistForms"]);
                }
                if (this.options.trackProductView) {
                    _rejoiner.push(['trackProductView', this.options.trackProductView]);
                }

                if (storageData.customerEmail) {
                    _rejoiner.push(['setCustomerEmail', storageData.customerEmail]);
                }

                if (storageData.customerData) {
                    _rejoiner.push(['setCustomerData', JSON.parse(storageData.customerData)]);
                }

                if (this.options.outputConversionData && storageData.convertionCartData && storageData.convertionCartItems) {
                    _rejoiner.push(["sendConversion", {
                        cart_data: storageData.convertionCartData,
                        cart_items: storageData.convertionCartItems
                    }]);

                    this.skipSubscription = true;
                    delete storageData.convertionCartData;
                    delete storageData.convertionCartItems;
                    customerData.set('rejoiner-acr', storageData);
                    this.skipSubscription = false;
                }
            }
            return _rejoiner;
        },


        connectRemoteScript: function() {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src =  'https://cdn.rejoiner.com/js/v4/rejoiner.lib.js';
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
        }

    });
    return $.rejoiner.acrTracking;
});