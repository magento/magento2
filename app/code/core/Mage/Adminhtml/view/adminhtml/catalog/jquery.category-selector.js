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
 * @package     js
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
(function ($, undefined) {
    "use strict";
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
                    '</li></ul></div>'
                ),
                $list = $element.children(),
                $this = $(this),
                name = $this.attr('name'),
                $searchField = $list.find('.category-selector-search-field'),
                itemRenderer = function(value, text, data) {
                    $('<li class="category-selector-search-choice button"/>')
                        .data(data || {})
                        .append($('<input type="hidden" />').attr('name', name).val(value))
                        .append($('<div/>').text(text))
                        .append('<span ' +
                            'class="category-selector-search-choice-close" tabindex="-1"></span>'
                        )
                        .insertBefore($searchField);
                },
                $input = $element.find('.category-selector-input'),
                elementPresent = function(item) {
                    var selector = '[name="product[category_ids][]"][value=' + parseInt(item.value, 10) + ']';
                    return $list.find(selector).length > 0;
                };
            $element.append($('<input type="hidden" />').attr('name', name));
            $this.find('option').each(function(){
                itemRenderer($(this).val(), $(this).text());
            });
            $this.attr('disabled', 'disabled').hide();
            $this.data('category-selector-element', $element);
            $element.insertAfter($this);
            $list.delegate(".category-selector-search-choice-close", "click", function() {
                $(this).parent().remove();
            });
            $input.bind('ajaxSend ajaxComplete', function(e) {
                e.stopPropagation();
                switch (e.type) {
                    case 'ajaxSend': $input.addClass('category-selector-active'); break;
                    case 'ajaxComplete': $input.removeClass('category-selector-active'); break;
                }
            });
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: options.url,
                        context: $input,
                        dataType: "json",
                        data: {
                            name_part: request.term
                        },
                        success: function(data) {
                            response(treeToList([], data || [], 0, ''));
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    if (elementPresent(ui.item)) {
                        event.preventDefault();
                        return false;
                    }
                    itemRenderer(ui.item.value, ui.item.label, ui.item);
                    $element.find('.category-selector-input').val('');
                    return false;
                },
                close: function(event) {
                    event.preventDefault();
                    return false;
                }
            });
            $input.data("autocomplete")._renderItem = function(ul, item) {
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
                if (window.parseInt(item.item.is_active, 10) == 0) {
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
