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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
(function ($) {
    'use strict';
    var treeToList = function(list, nodes, level, path) {
        $.each(nodes, function() {
            list.push({
                label: this.name,
                value: this.id,
                level: level,
                item: this,
                path: path + this.name
            });
            if ('children' in this) {
                treeToList(list, this.children, level + 1, path + this.name + '/' );
            }
        });
        return list;
    };
    $.fn.categorySelector = function (options) {
        this.each(function () {
            var $element = $(
                    '<div class="category-selector-container category-selector-container-multi">' +
                    '<ul class="category-selector-choices">' +
                    '<li class="category-selector-search-field">' +
                    '<input type="text" autocomplete="off" ' +
                        'data-ui-id="category-selector-input" class="category-selector-input">' +
                    '</li></ul></div>' +
                    '<button title="New Category" type="button" onclick="jQuery(\'#new-category\').dialog(\'open\')">' +
                        '<span><span><span>New Category</span></span></span>' +
                    '</button>'
                ),
                $list = $element.children(),
                $this = $(this),
                name = $this.attr('name'),
                $searchField = $list.find('.category-selector-search-field'),
                $input = $element.find('.category-selector-input'),
                elementPresent = function(item) {
                    var selector = '[name="product[category_ids][]"][value=' + parseInt(item.value, 10) + ']';
                    return $list.find(selector).length > 0;
                };

            $this.bind('categorySelector:add', function(event, args) {
                $('<li class="category-selector-search-choice button"/>')
                    .data(args.data || {})
                    .append($('<input type="hidden" />').attr('name', name).val(args.value))
                    .append($('<div/>').text(args.text))
                    .append('<span ' +
                        'class="category-selector-search-choice-close" tabindex="-1"></span>'
                    )
                    .insertBefore($searchField);
            });

            $element.append($('<input type="hidden" />').attr('name', name));
            $this.find('option').each(function() {
                $this.trigger('categorySelector:add', {
                    text: $(this).text(),
                    value: $(this).val()
                });
            });
            $this.attr('disabled', 'disabled').hide();
            $this.data('category-selector-element', $element);
            $element.insertAfter($this);
            $list.delegate('.category-selector-search-choice-close', 'click', function() {
                $(this).parent().remove();
            });
            $input.bind('ajaxSend ajaxComplete', function(e) {
                e.stopPropagation();
            });
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: options.url,
                        context: $input,
                        dataType: 'json',
                        data: {name_part: request.term},
                        success: function(data) {
                            response(treeToList([], data || [], 0, ''));
                        }
                    });
                },
                minLength: 0,
                focus: function(event, ui) {
                    $element.find('.category-selector-input').val(ui.item.label);
                    return false;
                },
                select: function(event, ui) {
                    if (elementPresent(ui.item)) {
                        event.preventDefault();
                        return false;
                    }
                    $this.trigger('categorySelector:add', {
                        text: ui.item.label,
                        value: ui.item.value,
                        data: ui.item
                    });
                    $element.find('.category-selector-input').val('');
                    return false;
                },
                close: function(event) {
                    event.preventDefault();
                    return false;
                }
            });
            $input.data('autocomplete')._renderItem = function(ul, item) {
                var level = window.parseInt(item.level),
                    $li = $("<li>");
                $li.data("item.autocomplete", item);
                $li.append($("<a />", {
                            'data-level': level,
                            'data-ui-id': 'category-selector-' + item.value
                        })
                        .attr('title', item.path)
                        .addClass('level-' + level)
                        .text(item.label)
                        .css({marginLeft: level * 16})
                    );
                if (window.parseInt(item.item.is_active, 10) === 0) {
                    $li.addClass('category-disabled');
                }
                if (elementPresent(item)) {
                    $li.addClass('category-selected');
                }
                $li.appendTo(ul);

                return $li;
            };
        });
    };
})(jQuery);
