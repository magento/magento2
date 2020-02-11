/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiElement',
    'ko',
    'jquery'
], function (Element, ko, $) {

    let url = $('#ajaxUrl').val();
    let count = 0;
    if (url === 'none') {
        $(".ko-btn").addClass("hidden-btn");
    }
    return Element.extend({
        defaults: {
            template: 'Mod_HelloWorldFrontendUi/mod_comment_saver_template'
        },

        hidden: function () {
            $(document).ready(function () {
                if (url === 'none') {
                    $(".ko-btn").addClass("hidden-btn");
                }
            })
        },
        /** @inheritdoc */
        initialize: function () {
            let comment = $('#extraComment').val();
            let sku = $('#ajaxSku').val();
            count++;
            this._super();
            $.ajax({
                method: "POST",
                url: url,
                data: {
                    comment: comment,
                    sku: sku
                },
                dataType: "json"
            })
                .done(function (msg) {
                    console.log('OK');
                    if (count > 1) {
                        $('#extraComment').val('');
                    }
                });
            },
        });
});
