/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'Magento_PaypalCaptcha/js/view/payment/list-mixin': true
            }
        }
    }
};
