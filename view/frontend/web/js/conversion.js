define([
    'jquery'
], function($) {
    'use strict';

    $.widget('rejoiner.acrConversion', {
        options: {
            rejoinerSiteId: '',
            rejoinerDomain: ''
        },

        _create: function() {
            window._rejoiner = this.getRejoinerObject();
            this.connectRemoteScript();

        },

        getRejoinerObject: function () {
            var _rejoiner = window._rejoiner || [];
            _rejoiner.push(["setAccount", this.options.rejoinerSiteId]);
            _rejoiner.push(["setDomain", this.options.rejoinerDomain]);
            _rejoiner.push(["sendConversion"]);
            return _rejoiner;
        },


        connectRemoteScript: function() {
            var s = document.createElement('script');
            s.type = 'text/javascript';
            s.async = true;
            s.src = 'https://s3.amazonaws.com/rejoiner/js/v3/t.js';
            var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x);
        }

    });
    return $.rejoiner.acrConversion;
});