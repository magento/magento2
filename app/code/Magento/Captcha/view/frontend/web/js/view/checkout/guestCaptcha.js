/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true jquery:true*/
/*global define*/
define(
    [
        'Magento_Captcha/js/view/checkout/defaultCaptcha'
    ],
    function (defaultCaptcha) {
        "use strict";
        return defaultCaptcha.extend({
            initialize: function() {
                this._super();
                //some init things
            }
        });
    }
);
