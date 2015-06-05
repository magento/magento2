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

    return Component.extend({
        initialize: function () {
            this._super();
            this.multiselect = registry.async(this.multiselectName);
        },
        title: 'step1',
        render: function (wizard) {
        },
        force: function (wizard) {
            //TODO: add validation
            return this.multiselect().selected();
        },
        back: function (wizard) {
            console.log(this.title + ':back');
        }
    });
});
