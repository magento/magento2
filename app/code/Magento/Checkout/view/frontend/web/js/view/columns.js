/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        '../model/quote'
    ],
    function (Component, quote) {
        return Component.extend({
            defaults: {
                template: '',
                displayArea: 'columns'
            }
        });
    }
);
