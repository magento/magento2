/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent'
    ],
    function (Component) {
        return Component.extend({
            getCode: function() {
                return this.index;
            },
            isActive: function(parent) {
                return false;
            },
            getData: function() {
                return {};
            },
            getInfo: function() {
                return [];
            },
            afterSave: function() {
                return true;
            },
            placeOrder: function() {
                alert('Kaboom!');
                // TODO: Place order info here
            }
        });
    }
);
