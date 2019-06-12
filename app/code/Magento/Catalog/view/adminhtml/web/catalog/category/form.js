/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';

    return function (config) {
        var categoryForm = {
            options: {
                categoryIdSelector: 'input[name="id"]',
                categoryPathSelector: 'input[name="path"]',
                categoryParentSelector: 'input[name="parent"]',
<<<<<<< HEAD
=======
                categoryLevelSelector: 'input[name="level"]',
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                refreshUrl: config.refreshUrl
            },

            /**
             * Sending ajax to server to refresh field 'path'
             * @protected
             */
            refreshPath: function () {
                if (!$(this.options.categoryIdSelector)) {
                    return false;
                }
                $.ajax({
                    url: this.options.refreshUrl,
                    method: 'GET',
                    showLoader: true
                }).done(this._refreshPathSuccess.bind(this));
            },

            /**
             * Refresh field 'path' on ajax success
             * @param {Object} data
             * @private
             */
            _refreshPathSuccess: function (data) {
                if (data.error) {
                    alert({
                        content: data.message
                    });
                } else {
                    $(this.options.categoryIdSelector).val(data.id).change();
                    $(this.options.categoryPathSelector).val(data.path).change();
                    $(this.options.categoryParentSelector).val(data.parentId).change();
<<<<<<< HEAD
=======
                    $(this.options.categoryLevelSelector).val(data.level).change();
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
                }
            }
        };

        $('body').on('categoryMove.tree', $.proxy(categoryForm.refreshPath.bind(categoryForm), this));
    };
});
