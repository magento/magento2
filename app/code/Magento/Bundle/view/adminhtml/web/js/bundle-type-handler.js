/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true expr:true*/
define([
    "jquery"
], function($){
    "use strict";

    return {
        'Magento_Bundle/js/bundle-type-handler': function(data) {
            this.bindAll();
        },
        bindAll: function () {
            $(document).on('changeTypeProduct', function (event, controllers) {
                if (controllers.type.real == 'bundle'
                    && controllers.type.current != 'bundle' && !controllers.weight.isLocked()) {
                    controllers.weight.switchWeight();
                }
            });
        }
    };
});
