/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'jquery/ui',
    'mage/dataPost',
    'mage/backend/validation',
    'Magento_Ui/js/modal/confirm'
], function ($, jqueryUi, dataPost, validation, modalConfirm) {
    'use strict';

    $.widget('mage.storeValidation', {

        /**
         * Validation creation
         * @protected
         */
        _create: function () {
            var form = this.element[0],
                validator = $.data(form, 'validator');

            if (validator && validator.settings) {
                validator.settings.submitHandler = this._saveHandler;
                validator.settings.confirmCallback = this._needConfirm;
                $.extend(validator.settings, this.options);
                $.data(form, 'validator', validator);
            }
        },

        /**
         * Check is it need to show confirmation popup
         *
         * @returns {Boolean}
         */
        _needConfirm: function () {
            return true;
        },

        /**
         * Save form with confirmation if needed
         *
         * @param {Object} form
         * @private
         */
        _saveHandler: function (form) {
            var formData = {},
                requestData = {},
                options = $.data(form, 'validator').settings;

            if ($(form).validation('isValid')) {
                $.each($(form).serializeArray(), function () {
                    formData[this.name] = this.value || '';
                });
                requestData = {
                    action: $(form).attr('action'),
                    data: formData
                };

                if (options.confirmCallback.call(this)) {
                    modalConfirm({
                        title: $.mage.__('Warning message'),
                        content: $.mage.__('This operation can take a long time'),
                        actions: {
                            /**
                             * 'Confirm' action handler.
                             */
                            confirm: function () {
                                dataPost().postData(requestData);
                            }
                        }
                    });
                } else {
                    dataPost().postData(requestData);
                }
            }
        }
    });

    return $.mage.storeValidation;
});
