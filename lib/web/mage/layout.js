/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
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
