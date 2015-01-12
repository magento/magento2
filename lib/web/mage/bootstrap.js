/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true browser:true */
define([
    "jquery",
    "mage/apply/main"
], function($, mage){
    'use strict';
    
    $.ajaxSetup({
        cache: false
    });

    var bootstrap = function() {
        /**
         * Init all components defined via data-mage-init attribute
         * and subscribe init action to contentUpdated event
         */
        mage.apply();
    };

    $(bootstrap);
});
