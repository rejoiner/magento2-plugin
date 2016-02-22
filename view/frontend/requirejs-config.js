/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    paths: {
        RejoinerAcrTracking   : 'Rejoiner_Acr/js/tracking',
        RejoinerAcrConversion : 'Rejoiner_Acr/js/conversion'
    },
    shim: {
        RejoinerAcrTracking: [
            "https://s3.amazonaws.com/rejoiner/js/v3/t.js"
        ],
        RejoinerAcrConversion: [
            "https://s3.amazonaws.com/rejoiner/js/v3/t.js"
        ]
    }
};