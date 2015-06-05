/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "uiComponent",
    "jquery"
], function (Component, $) {
    "use strict";

    return Component.extend({
        initialize: function () {
            this._super();
        },
        title: 'step1',
        render: function (wizard) {
            $.ajax({
                type: "POST",
                url: this.options_url,
                data: {attributes: wizard.data},
                showLoader: true,
                success: function (res) {
                    console.log(res)
                }
            });

        },
        force: function (wizard) {
        },
        back: function (wizard) {
        }
    });
});
