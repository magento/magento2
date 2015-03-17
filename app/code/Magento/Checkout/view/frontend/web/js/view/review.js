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
        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/review'
            }
        });
    }
);
