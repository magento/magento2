/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(function() {
    var errors = [];
    return {
        add: function (error) {
            errors.push(error);
        },
        remove: function() {
            errors.shift();
        },
        getAll: function () {
            return errors;
        }
    }
});
