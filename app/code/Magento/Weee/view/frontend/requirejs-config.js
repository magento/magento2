/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

var config = {
    map: {
        '*': {
            'taxToggle': 'Magento_Weee/js/tax-toggle',
            'Magento_Weee/tax-toggle': 'Magento_Weee/js/tax-toggle'
        }
    },
    config: {
        mixins: {
            'Magento_Catalog/js/price-box': {
                'Magento_Weee/js/price-box-mixin': true
            }
        }
    }
};
