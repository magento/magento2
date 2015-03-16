/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([], function() {
    var addresses = [];
    return {
        add: function (address) {
            addresses.push(address);
        }
    }
});
