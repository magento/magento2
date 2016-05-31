/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint regexdash:true eqnull:true browser:true jquery:true*/
define([], function () {
    var baseUrl = '';
    return {
        getBaseUrl: function () {
            return this.values.baseUrl;
        },
        getFormKey: function() {
            return this.values.formKey;
        }
    }
});
