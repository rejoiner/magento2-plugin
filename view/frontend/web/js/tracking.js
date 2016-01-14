define([
    'jquery'
], function($) {
    'use strict';

    $.widget('rejoiner.acrTracking', {
        options: {
            rejoinerSiteId: '',
            rejoinerDomain: '',
            cartItems: [],
            removedItems: [],
            trackNumberEnabled: '',
            persistFormsEnabled: '',
            cartData: ''
        },

        _create: function() {

            window._rejoiner = this.getRejoinerObject();
            this.connectRemoteScript();

        },

        getRejoinerObject: function () {
            var _rejoiner = window._rejoiner || [];
            _rejoiner.push(["setAccount", this.options.rejoinerSiteId]);
            _rejoiner.push(["setDomain", this.options.rejoinerDomain]);
            if (this.options.trackNumberEnabled) {
                _rejoiner.push(["trackNumbers"]);
            }
            if (this.options.persistFormsEnabled) {
                _rejoiner.push(["persistForms"]);
            }
            if (this.options.cartData) {
                _rejoiner.push(["setCartData", this.options.cartData]);
            }

            if (this.options.cartItems) {
                this.options.cartItems.forEach(function(element) {
                    _rejoiner.push(["setCartItem", element]);
                });
            }
            if (this.options.removedItems) {
                this.options.removedItems.forEach(function(element) {
                    _rejoiner.push(["removeCartItem", {product_id: element}]);
                });
            }
            return _rejoiner;
        },


        connectRemoteScript: function() {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = 'https://s3.amazonaws.com/rejoiner/js/v3/t.js';
            var x = document.getElementsByTagName('script')[0];
            x.parentNode.insertBefore(s, x);
        }

    });
    return $.rejoiner.acrTracking;
});