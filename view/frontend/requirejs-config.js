/*
 * Copyright © 2022 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    map: {
        '*': {
            RejoinerAcrTracking   : 'Rejoiner_Acr/js/tracking'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Rejoiner_Acr/js/checkout/action/set-shipping-information-mixin': true
            }
        }
    }
};
