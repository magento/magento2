/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true */
/*global console:true*/
define(['underscore'], function($) {
    return {
        build: function(config) {
            var types = _.map(_.flatten(config), function(item) {
                return item.type;
            });
            require(types, function () {
                
            });
        }
    };
});
