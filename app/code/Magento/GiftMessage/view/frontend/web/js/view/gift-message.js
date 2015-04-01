/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['uiComponent'],
    function (Component) {
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/gift-message',
                displayArea: 'giftMessage'
            }
        });
    }
);
