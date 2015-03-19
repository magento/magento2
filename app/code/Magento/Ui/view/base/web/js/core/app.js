/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './renderer/renderer',
    'Magento_Ui/js/lib/registry/registry'
], function (_, Renderer, registry) {
    'use strict';

    var global = {
        init: function (data) {
            this.data = {};

            this.register()
                .initRenderer(data);
        },

        initRenderer: function (data) {
            this.renderer = new Renderer(data);

            return this;
        },

        register: function () {
            registry.set('globalStorage', this);

            return this;
        }
    };

    return global.init.bind(global);
});
