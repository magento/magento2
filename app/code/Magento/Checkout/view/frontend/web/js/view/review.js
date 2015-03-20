/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['Magento_Ui/js/form/component'],
    function (Component) {
        var itemsBefore = [];
        var itemsAfter = [];

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review',
                itemsBefore: itemsBefore,
                itemsAfter: itemsAfter,
                getItems: function() {},
                getAgreementsTemplate: function() {}
            }
        });
    }
);
