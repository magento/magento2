/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "uiComponent",
    "jquery",
    "uiRegistry"
], function (Component, $, registry) {
    "use strict";

    var initNewAttributeListener = function (provider) {
        $('#configurable-attributes-container').on('add', function() {
            provider().reload();
        });
    };
    return Component.extend({
        initialize: function () {
            this._super();
            this.multiselect = registry.async(this.multiselectName);
            initNewAttributeListener(registry.async(this.providerName));
        },
        render: function (wizard) {
        },
        force: function (wizard) {
            wizard.data.attributes = this.multiselect().selected();

            if (!wizard.data.attributes || wizard.data.attributes.length === 0) {
                throw new Error($.mage.__('Please, select attribute(s)'));
            }
        },
        back: function (wizard) {
        }
    });
});
