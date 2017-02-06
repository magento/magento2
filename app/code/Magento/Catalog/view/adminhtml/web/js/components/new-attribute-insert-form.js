/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form'
], function (InsertForm) {
    'use strict';

    return InsertForm.extend({
        defaults: {
            modules: {
                productForm: 'product_form.product_form'
            },
            listens: {
                responseStatus: 'processResponseStatus'
            },
            attributeSetId: 0,
            productId: 0
        },

        /**
         * Process response status.
         */
        processResponseStatus: function () {
            if (this.responseStatus()) {

                if (this.productForm().params === undefined) {
                    this.productForm().params = {
                        set: this.attributeSetId
                    };
                }

                if (this.productId) {
                    this.productForm().params.id = this.productId;
                }
                this.productForm().params.type = this.productType;

                this.productForm().reload();
                this.resetForm();
            }
        }
    });
});
