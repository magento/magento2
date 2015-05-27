/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', '../model/errorlist'], function (Component, errors) {
    "use strict";
    return Component.extend({
        errorList: errors.getAll(),
        defaults: {
            template: 'Magento_Ui/errors'
        }
    });
});
