/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(['uiComponent', '../model/gift-options'],
    function (Component, giftOptions) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Magento_GiftMessage/Item-level-gift-message',
                displayArea: 'itemLevelGiftMessage'
            },
            initialize: function() {
                this._super();
                giftOptions.addItemLevelGiftOptions(this);
            }
        });
    }
);
