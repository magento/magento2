/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery-ui-modules/widget',
    'mage/validation/validation',
    'mage/dataPost'
], function ($, mageTemplate, alert) {
    'use strict';

    $.widget('mage.wishlist', {
        options: {
            dataAttribute: 'item-id',
            nameFormat: 'qty[{0}]',
            btnRemoveSelector: '[data-role=remove]',
            qtySelector: '[data-role=qty]',
            addToCartSelector: '[data-role=tocart]',
            addAllToCartSelector: '[data-role=all-tocart]',
            commentInputType: 'textarea',
            infoList: false
        },

        /**
         * Bind handlers to events.
         */
        _create: function () {
            var _this = this;

            if (!this.options.infoList) {
                this.element
                    .on('addToCart', function (event, context) {
                        var urlParams;

                        event.stopPropagation(event);
                        $(context).data('stop-processing', true);
                        urlParams = _this._getItemsToCartParams(
                            $(context).parents('[data-row=product-item]').find(_this.options.addToCartSelector)
                        );
                        $.mage.dataPost().postData(urlParams);

                        return false;
                    })
                    .on('click', this.options.btnRemoveSelector, $.proxy(function (event) {
                        event.preventDefault();
                        $.mage.dataPost().postData($(event.currentTarget).data('post-remove'));
                    }, this))
                    .on('click', this.options.addToCartSelector, $.proxy(this._beforeAddToCart, this))
                    .on('click', this.options.addAllToCartSelector, $.proxy(this._addAllWItemsToCart, this))
                    .on('focusin focusout', this.options.commentInputType, $.proxy(this._focusComment, this));
            }

            // Setup validation for the form
            this.element.mage('validation', {
                /** @inheritdoc */
                errorPlacement: function (error, element) {
                    error.insertAfter(element.next());
                }
            });
        },

        /**
         * Process data before add to cart
         *
         * - update item's qty value.
         *
         * @param {Event} event
         * @private
         */
        _beforeAddToCart: function (event) {
            var elem = $(event.currentTarget),
                itemId = elem.data(this.options.dataAttribute),
                qtyName = $.validator.format(this.options.nameFormat, itemId),
                qtyValue = elem.parents().find('[name="' + qtyName + '"]').val(),
                params = elem.data('post');

            if (params) {
                params.data = $.extend({}, params.data, {
                    'qty': qtyValue
                });
                elem.data('post', params);
            }
        },

        /**
         * Add wish list items to cart.
         * @private
         * @param {jQuery} elem - clicked 'add to cart' button
         */
        _getItemsToCartParams: function (elem) {
            var itemId, url, qtyName, qtyValue;

            if (elem.data(this.options.dataAttribute)) {
                itemId = elem.data(this.options.dataAttribute);
                url = this.options.addToCartUrl;
                qtyName = $.validator.format(this.options.nameFormat, itemId);
                qtyValue = elem.parents().find('[name="' + qtyName + '"]').val();
                url.data.item = itemId;
                url.data.qty = qtyValue;

                return url;
            }
        },

        /**
         * Add all wish list items to cart
         * @private
         */
        _addAllWItemsToCart: function () {
            var urlParams = this.options.addAllToCartUrl,
                separator = urlParams.action.indexOf('?') >= 0 ? '&' : '?';

            this.element.find(this.options.qtySelector).each(function (index, element) {
                urlParams.action += separator + $(element).prop('name') + '=' + encodeURIComponent($(element).val());
                separator = '&';
            });
            $.mage.dataPost().postData(urlParams);
        },

        /**
         * Toggle comment string.
         * @private
         * @param {Event} e
         */
        _focusComment: function (e) {
            var commentInput = e.currentTarget;

            if (commentInput.value === '' || commentInput.value === this.options.commentString) {
                commentInput.value = commentInput.value === this.options.commentString ?
                    '' : this.options.commentString;
            }
        }
    });

    // Extension for mage.wishlist - Select All checkbox
    $.widget('mage.wishlist', $.mage.wishlist, {
        options: {
            selectAllCheckbox: '#select-all',
            parentContainer: '#wishlist-table'
        },

        /** @inheritdoc */
        _create: function () {
            var selectAllCheckboxParent, checkboxCount;

            this._super();
            selectAllCheckboxParent = $(this.options.selectAllCheckbox).parents(this.options.parentContainer);
            checkboxCount = selectAllCheckboxParent
                .find('input:checkbox:not(' + this.options.selectAllCheckbox + ')').length;
            // If Select all checkbox is checked, check all item checkboxes, if unchecked, uncheck all item checkboxes
            $(this.options.selectAllCheckbox).on('click', function () {
                selectAllCheckboxParent.find('input:checkbox').attr('checked', $(this).is(':checked'));
            });
            // If all item checkboxes are checked, check select all checkbox,
            // if not all item checkboxes are checked, uncheck select all checkbox
            selectAllCheckboxParent.on(
                'click',
                'input:checkbox:not(' + this.options.selectAllCheckbox + ')',
                $.proxy(function () {
                    var checkedCount = selectAllCheckboxParent
                        .find('input:checkbox:checked:not(' + this.options.selectAllCheckbox + ')').length;

                    $(this.options.selectAllCheckbox).attr('checked', checkboxCount === checkedCount);
                }, this)
            );
        }
    });
    // Extension for mage.wishlist info add to cart
    $.widget('mage.wishlist', $.mage.wishlist, {
        /** @inheritdoc */
        _create: function () {
            this._super();

            if (this.options.infoList) {
                this.element.on('addToCart', $.proxy(function (event, context) {
                    this.element.find('input:checkbox').attr('checked', false);
                    $(context).closest('tr').find('input:checkbox').attr('checked', true);
                    this.element.submit();
                }, this));
                this._checkBoxValidate();
            }
        },

        /**
         * validate checkbox selection.
         * @private
         */
        _checkBoxValidate: function () {
            this.element.validation({
                submitHandler: $.proxy(function (form) {
                    if ($(form).find('input:checkbox:checked').length) {
                        form.submit();
                    } else {
                        alert({
                            content: this.options.checkBoxValidationMessage
                        });
                    }
                }, this)
            });
        }
    });

    // Extension for mage.wishlist - Add Wishlist item to Gift Registry
    $.widget('mage.wishlist', $.mage.wishlist, {
        options: {
            formTmplSelector: '#form-tmpl',
            formTmplId: '#wishlist-hidden-form'
        },

        /** @inheritdoc */
        _create: function () {
            var _this = this;

            this._super();
            this.element.on('click', '[data-wishlist-to-giftregistry]', function () {
                var json = $(this).data('wishlist-to-giftregistry'),
                    tmplJson = {
                        item: json.itemId,
                        entity: json.entity,
                        url: json.url
                    },
                    html = mageTemplate(_this.options.formTmplSelector, {
                        data: tmplJson
                    });

                $(html).appendTo('body');
                $(_this.options.formTmplId).submit();
            });
        }
    });

    return $.mage.wishlist;
});
