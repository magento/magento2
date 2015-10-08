/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery"
], function($) {

    /**
     * Type Switcher
     *
     * @param {object} data
     * @constructor
     */
    var TypeSwitcher = function (data) {
        this.$type = $('#product_type_id');
        this.$weight = $('#' + data.weight_id);
        this.$weight_switcher = $(data.weight_switcher);
        this.$tab = $('#' + data.tab_id);
        this.productHasWeight = function () {
            return $('input:checked', this.$weight_switcher).val() == data.product_has_weight_flag;
        };
        this.notifyProductWeightIsChanged = function () {
            return $('input:checked', this.$weight_switcher).trigger('change');
        };
        this.hideSwitcher = function () {
            this.$weight_switcher.hide();
        };

        if (!this.productHasWeight()) {
            this.baseType = {
                virtual: this.$type.val(),
                real: 'simple'
            };
        } else {
            this.baseType = {
                virtual: 'virtual',
                real: this.$type.val()
            };
        }
    };
    $.extend(TypeSwitcher.prototype, {
        /** @lends {TypeSwitcher} */

        /**
         * Bind event
         */
        bindAll: function () {
            this.$type.on('change', function() {
                this._switchToType(this.$type.val());
            }.bind(this));

            $('[data-form=edit-product] [data-role=tabs]').on('contentUpdated', function() {
                this._switchToType(this.$type.val());
                this.notifyProductWeightIsChanged();
            }.bind(this));

            $("#product_info_tabs").on("beforePanelsMove tabscreate tabsactivate", function() {
                this._switchToType(this.$type.val());
                this.notifyProductWeightIsChanged();
            }.bind(this));

            $(document).on('typeSwitcherOut', function () {
                $('input:not(:checked)', this.$weight_switcher).trigger('click');
            }.bind(this));

            $('input', this.$weight_switcher).on('change', this.changeType.bind(this)).trigger('change');
        },

        changeType: function() {
            $(document).trigger('typeSwitcher', {
                hasWeight: this.productHasWeight()
            });
            if (!this.productHasWeight()) {
                this.$type.val(this.baseType.virtual).trigger('change');
                if (this.$type.val() != 'bundle') { // @TODO move this check to Magento_Bundle after refactoring as widget
                    this.disableElement(this.$weight);
                }
                this.$tab.show().closest('li').removeClass('removed');
            } else {
                this.$type.val(this.baseType.real).trigger('change');
                if (this.$type.val() != 'bundle') { // @TODO move this check to Magento_Bundle after refactoring as widget
                    this.enableElement(this.$weight);
                }
                this.$tab.hide();
            }
        },

        /**
         * Disable element
         * @param {jQuery|HTMLElement} element
         */
        disableElement: function (element) {
            if (!this._isLocked(element)) {
                element.addClass('ignore-validate').prop('disabled', true);
            }
        },

        /**
         * Enable element
         * @param {jQuery|HTMLElement} element
         */
        enableElement: function (element) {
            if (!this._isLocked(element)) {
                element.removeClass('ignore-validate').prop('disabled', false);
            }
        },

        /**
         * Is element locked
         *
         * @param {jQuery|HTMLElement} element
         * @returns {Boolean}
         * @private
         */
        _isLocked: function (element) {
            return element.is('[data-locked]');
        },

        /**
         * Get element bu code
         * @param {string} code
         * @return {jQuery|HTMLElement}
         */
        getElementByCode: function(code) {
            return $('#attribute-' + code + '-container');
        },

        /**
         * Show/hide elements based on type
         *
         * @param {string} typeCode
         * @private
         */
        _switchToType: function(typeCode) {
            $('[data-apply-to]:not(.removed)').each(function(index, element) {
                var attrContainer = $(element),
                    applyTo = attrContainer.data('applyTo') || [];
                var $inputs = attrContainer.find('select, input, textarea');
                if (applyTo.length === 0 || $.inArray(typeCode, applyTo) !== -1) {
                    attrContainer.removeClass('not-applicable-attribute');
                    $inputs.removeClass('ignore-validate');
                } else {
                    attrContainer.addClass('not-applicable-attribute');
                    $inputs.addClass('ignore-validate');
                }
            });
        }
    });
    return TypeSwitcher;
});
