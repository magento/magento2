/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true sub:true*/
/*global alert*/
/*global Handlebars*/
define([
    "jquery",
    "handlebars",
    "jquery/ui",
    "mage/validation/validation",
    "mage/dataPost"
], function($){
    "use strict";

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
        _create: function() {
            var _this = this;
            if (!this.options.infoList) {
                this.element
                    .on('click', this.options.addToCartSelector, function() {
                        $.proxy(_this._addItemsToCart($(this)), _this);
                    })
                    .on('addToCart', function(event, context) {
                        $.proxy(_this._addItemsToCart($(context).parents('.cart-cell').find(_this.options.addToCartSelector)), _this);
                    })
                    .on('click', this.options.btnRemoveSelector, $.proxy(function(event) {
                        event.preventDefault();
                        $.mage.dataPost().postData($(event.currentTarget).data('post-remove'));
                    }, this))
                    .on('click', this.options.addAllToCartSelector, $.proxy(this._addAllWItemsToCart, this))
                    .on('focusin focusout', this.options.commentInputType, $.proxy(this._focusComment, this));
            }

			// Setup validation for the form
			this.element.mage('validation', {
				errorPlacement: function(error, element) { 
					error.insertAfter(element.next()); 
				}
			});
        },

        /**
         * Validate and Redirect.
         * @private
         * @param {string} url
         */
        _validateAndRedirect: function(url) {
            if (this.element.validation({
                errorPlacement: function(error, element) {
                    error.insertAfter(element.next());
                }
            }).valid()) {
                this.element.prop('action', url);
                window.location.href = url;
            }
        },

        /**
         * Add wish list items to cart.
         * @private
         * @param {jQuery object} elem - clicked 'add to cart' button
         */
        _addItemsToCart: function(elem) {
            if (elem.data(this.options.dataAttribute)) {
                var itemId = elem.data(this.options.dataAttribute),
                    url = this.options.addToCartUrl.replace('%item%', itemId),
                    inputName = $.validator.format(this.options.nameFormat, itemId),
                    inputValue = elem.parent().find('[name="' + inputName + '"]').val(),
                    separator = (url.indexOf('?') >= 0) ? '&' : '?';
                url += separator + inputName + '=' + encodeURIComponent(inputValue);
                this._validateAndRedirect(url);
                return;
            }

        },

        /**
         * Add all wish list items to cart
         * @private
         */
        _addAllWItemsToCart: function() {
            var url = this.options.addAllToCartUrl,
                separator = (url.indexOf('?') >= 0) ? '&' : '?';
            this.element.find(this.options.qtySelector).each(function(index, element) {
                url += separator + $(element).prop('name') + '=' + encodeURIComponent($(element).val());
                separator = '&';
            });
            this._validateAndRedirect(url);
        },

        /**
         * Toggle comment string.
         * @private
         * @param {event} e
         */
        _focusComment: function(e) {
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

        _create: function() {
            this._super();
            var selectAllCheckboxParent = $(this.options.selectAllCheckbox).parents(this.options.parentContainer),
                checkboxCount = selectAllCheckboxParent.find('input:checkbox:not(' + this.options.selectAllCheckbox + ')').length;
            // If Select all checkbox is checked, check all item checkboxes, if unchecked, uncheck all item checkboxes
            $(this.options.selectAllCheckbox).on('click', function() {
                selectAllCheckboxParent.find('input:checkbox').attr('checked', $(this).is(':checked'));
            });
            // If all item checkboxes are checked, check select all checkbox,
            // if not all item checkboxes are checked, uncheck select all checkbox
            selectAllCheckboxParent.on('click', 'input:checkbox:not(' + this.options.selectAllCheckbox + ')', $.proxy(function() {
                var checkedCount = selectAllCheckboxParent.find('input:checkbox:checked:not(' + this.options.selectAllCheckbox + ')').length;
                $(this.options.selectAllCheckbox).attr('checked', checkboxCount === checkedCount);
            }, this));
        }
    });
    // Extension for mage.wishlist info add to cart
    $.widget('mage.wishlist', $.mage.wishlist, {
        _create: function() {
            this._super();
            if (this.options.infoList) {
                this.element.on('addToCart', $.proxy(function(event, context) {
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
        _checkBoxValidate: function() {
            this.element.validation({
                submitHandler: $.proxy(function(form) {
                    if ($(form).find('input:checkbox:checked').length) {
                        form.submit();
                    } else {
                        alert(this.options.checkBoxValidationMessage);
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

        _create: function() {
            this._super();
            var _this = this;
            this.element.on('click', '[data-wishlist-to-giftregistry]', function() {
                var json = $(this).data('wishlist-to-giftregistry'),
                    tmplJson = {
                        item: json['itemId'],
                        entity: json['entity'],
                        url: json['url']
                    },
                    source = $(_this.options.formTmplSelector).html(),
                    template = Handlebars.compile(source),
                    html = template(tmplJson);
                    $(html).appendTo('body');
                $(_this.options.formTmplId).submit();
            });
        }
    });

    return $.mage.wishlist;
});