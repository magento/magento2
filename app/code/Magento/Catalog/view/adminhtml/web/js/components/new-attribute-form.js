/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Ui/js/modal/prompt',
    'Magento_Ui/js/modal/alert'
], function ($, Form, prompt, alert) {
    'use strict';

    return Form.extend({
        defaults: {
            newSetPromptMessage: '',
            listens: {
                responseData: 'processResponseData'
            },
            modules: {
                productForm: 'product_form.product_form'
            }
        },

        /**
         * Process response data
         *
         * @param {Object} data
         */
        processResponseData: function (data) {
            if (data.params['new_attribute_set_id']) {
                this.productForm().params = {
                    set: data.params['new_attribute_set_id']
                };
            }
        },

        /**
         * Process Save In New Attribute Set prompt
         */
        saveAttributeInNewSet: function () {

            var self = this;

            prompt({
                content: this.newSetPromptMessage,
                actions: {

                    /**
                     * @param {String} val
                     * @this {actions}
                     */
                    confirm: function (val) {
                        var rules = ['required-entry', 'validate-no-html-tags'],
                            editForm = self,
                            newAttributeSetName = val,
                            i,
                            params = {};

                        if (!newAttributeSetName) {
                            return;
                        }

                        for (i = 0; i < rules.length; i++) {
                            if (!$.validator.methods[rules[i]](newAttributeSetName)) {
                                alert({
                                    content: $.validator.messages[rules[i]]
                                });

                                return;
                            }
                        }

                        params['new_attribute_set_name'] = newAttributeSetName;
                        editForm.setAdditionalData(params);
                        editForm.save();
                    }
                }
            });
        }
    });
});
