/**
 * Copyright © 2017 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        },

        getRejoinerObject: function () {
            var _rejoiner = window._rejoiner || [];
            _rejoiner.push(["setAccount", this.options.rejoinerSiteId]);
            _rejoiner.push(["setDomain", this.options.rejoinerDomain]);
            _rejoiner.push(["sendConversion"]);
            return _rejoiner;
        }
    });
    return $.rejoiner.acrConversion;
});