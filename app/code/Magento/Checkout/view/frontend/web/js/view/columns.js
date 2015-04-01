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
        'Magento_Ui/js/form/component',
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
