/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "uiComponent",
    "jquery",
    'uiRegistry'
], function (Component, $, registry) {
    "use strict";

    var initNewAttributeListener = function (provider) {
        var $form = $('[data-form=edit-product]');
        $form.on('changeAttributeSet', function() {
            provider().reload();
        });
    };
    return Component.extend({
        initialize: function () {
            this._super();
            this.multiselect = registry.async(this.multiselectName);
            initNewAttributeListener(registry.async(this.providerName));
        },
        title: 'step1',
        render: function (wizard) {
        },
        force: function (wizard) {
            //TODO: add validation
            wizard.data.attributes = this.multiselect().selected();
        },
        back: function (wizard) {
            console.log(this.title + ':back');
        }
    });
});
