/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form',
], function (InsertForm) {
    'use strict';

    return InsertForm.extend({
        defaults: {
            modules: {
                productForm: 'product_form.product_form',
            },
            listens: {
                responseStatus: 'processResponseStatus',

            },
            attributeSetId: 0,
            productId: 0
        },

        processResponseStatus: function () {
            if (this.responseStatus()) {
                this.productForm().params = {
                    set: this.attributeSetId,
                    id: this.productId,
                    type: this.productType
                };
                this.productForm().reload();
                this.resetForm();
            }
        }
    });
});
