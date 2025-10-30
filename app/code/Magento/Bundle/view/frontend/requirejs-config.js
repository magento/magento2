/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

var config = {
    map: {
        '*': {
            bundleOption:   'Magento_Bundle/bundle',
            priceBundle:    'Magento_Bundle/js/price-bundle',
            slide:          'Magento_Bundle/js/slide',
            productSummary: 'Magento_Bundle/js/product-summary'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Magento_Bundle/js/validation': true
            }
        }
    }
};
