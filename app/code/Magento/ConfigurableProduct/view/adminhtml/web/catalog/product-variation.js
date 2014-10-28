/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
define([
    "jquery",
    "jquery/ui",
    "jquery/template",
    "js/theme"
], function($){

    $.widget('mage.variationsAttributes', {
        _create: function () {
            var widgetContainer = $(this.element);
            widgetContainer.sortable({
                axis: 'y',
                handle: '.draggable-handle',
                tolerance: 'pointer',
                update: function () {
                    $(this).find('[name$="[position]"]').each(function (index) {
                        $(this).val(index);
                    });
                }
            });
            var updateGenerateVariationsButtonAvailability = function () {
                var isDisabled = widgetContainer.find(
                        '[data-role=configurable-attribute]:not(:has(input[name$="[include]"]:checked))'
                    ).length > 0 || !widgetContainer.find('[data-role=configurable-attribute]').length;
                widgetContainer.closest('[data-panel=product-variations]')
                    .find('[data-action=generate]').prop('disabled', isDisabled).toggleClass('disabled', isDisabled);
            };

            this._on({
                'menuselect [data-column=change-price] [data-role=dropdown-menu]': function (event, ui) {
                    var parent = $(event.target).closest('[data-column=change-price]');
                    parent.find('[data-toggle=dropdown] span').text(ui.item.text());
                    parent.find('[name$="[is_percent]"]').val(ui.item.data('value'));
                    parent.find('[data-toggle=dropdown]').trigger('close.dropdown');
                    $(event.target).find('[data-value]').show();
                    ui.item.hide();
                },
                'click .fieldset-wrapper-title .action-delete': function (event) {
                    var $entity = $(event.target).closest('[data-role=configurable-attribute]');
                    $('#attribute-' + $entity.find('[name$="[code]"]').val() + '-container select').removeAttr('disabled');
                    $entity.remove();
                    updateGenerateVariationsButtonAvailability();
                    event.stopImmediatePropagation();
                },
                'click [data-column=actions] [data-action=delete]':  function (event) {
                    $(event.target).closest('[data-role=option-container]').remove();
                    updateGenerateVariationsButtonAvailability();
                    event.stopPropagation();
                },
                'click .toggle': function (event) {
                    $(event.target).parent().next('fieldset').toggle();
                },
                'click input.include': updateGenerateVariationsButtonAvailability,
                add: function (event, attribute) {
                    widgetContainer.find('[data-template-for=configurable-attribute]').tmpl({attribute: attribute})
                        .appendTo($(event.target)).trigger('contentUpdated')
                        .find('[data-attribute-id]').collapsable().collapse('show')
                        .find('[data-role=dropdown-menu]').each(function (index, element) {
                            $(element).trigger('menuselect', {item: $(element).find('[data-value="0"]')});
                        });
                    $('#attribute-' + attribute.code + '-container select').prop('disabled', true);
                    $('[data-store-label]').useDefault();
                },
                contentUpdated: updateGenerateVariationsButtonAvailability
            });
            this.element.find('[data-column=change-price]').each(function (index, element) {
                $(element).find('[data-role=dropdown-menu]').trigger('menuselect', {
                    item: $(element).find('[data-value="' + $(element).find('[name$="[is_percent]"]').val() + '"]')
                });
            });
            updateGenerateVariationsButtonAvailability();
            if ($('[data-form=edit-product]').data('product-id')) {
                widgetContainer.find('[data-attribute-id]').collapsable().collapse('hide');
            }
        },
        /**
         * Retrieve list of attributes
         *
         * @return {Array}
         */
        getAttributes: function () {
            return $.map(
                $(this.element).find('[data-role=configurable-attribute]') || [],
                function (attribute) {
                    var $attribute = $(attribute);
                    return {
                        id: $attribute.find('[name$="[attribute_id]"]').val(),
                        code: $attribute.find('[name$="[code]"]').val(),
                        label: $attribute.find('[name$="[label]"]').val(),
                        position: $attribute.find('[name$="[position]"]').val()
                    };
                }
            );
        }
    });

});