/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.dataPost', {
        options: {
            formTemplate: '<form action="<%- data.action %>" method="post" hidden>' +
            '<% _.each(data.data, function(value, index) { %>' +
            '<input name="<%- index %>" value="<%- value %>">' +
            '<% }) %></form>',
            postTrigger: ['a[data-post]', 'button[data-post]', 'span[data-post]'],
            formKeyInputSelector: 'input[name="form_key"]'
        },

        /** @inheritdoc */
        _create: function () {
            this._bind();
        },

        /** @inheritdoc */
        _bind: function () {
            var events = {};

            $.each(this.options.postTrigger, function (index, value) {
                events['click ' + value] = '_postDataAction';
            });

            this._on(events);
        },

        /**
         * Handler for click.
         *
         * @param {Object} e
         * @private
         */
        _postDataAction: function (e) {
            var params = $(e.currentTarget).data('post');

            e.preventDefault();
            this.postData(params);
        },

        /**
         * Data post action.
         *
         * @param {Object} params
         */
        postData: function (params) {
            var $form,
                formKey = $(this.options.formKeyInputSelector).val(),
                paramsData = params.data;

            if (formKey) {
                paramsData['form_key'] = formKey;
            }

            require(['mage/template'], function(mageTemplate) {
                $form = $(mageTemplate(this.options.formTemplate, {
                    data: params
                }));
    
                if (params.files) {
                    $form.prop('enctype', 'multipart/form-data');
    
                    $.each(params.files, function (inputName, files) {
                        if (files instanceof FileList) {
                            $form.append(
                                $('<input>').prop({
                                    type: 'file',
                                    name: inputName,
                                    file: files
                                })
                            );
                        }
                    });
                }
    
                if (paramsData.confirmation) {
                    require(['Magento_Ui/js/modal/confirm'], function (uiConfirm) {
                        uiConfirm({
                            content: paramsData.confirmationMessage,
                            actions: {
                                /** @inheritdoc */
                                confirm: function () {
                                    $form.appendTo('body').submit();
                                }
                            }
                        });
                    });
                } else {
                    $form.appendTo('body').submit();
                }
            });
        }
    });

    $(document).dataPost();

    return $.mage.dataPost;
});
