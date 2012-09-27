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
 * @category    catalogsearch search
 * @package     mage
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint browser:true jquery:true*/
(function ($) {
    $(document).ready(function () {

        var searchInit = {
            // Default values
            minSearchLength: 2,
            responseFieldElements: 'ul li',
            selectClass: 'selected',
            // Filled in initialization event
            placeholder: null,
            destinationSelector: null,
            fieldSelector: null,
            formSelector: null
        };
        // Trigger initialize event
        $.mage.event.trigger('mage.catalogsearch.initialize', searchInit);

        var responseList = {
            indexList: null,
            selected: null
        };

        function getFirstVisibleElement() {
            if (responseList.indexList) {
                var firstElement = responseList.indexList.first();
                return  firstElement.is(':visible') ? firstElement : firstElement.next();
            }
            return false;
        }

        function getLastElement() {
            if (responseList.indexList) {
                return responseList.indexList.last();
            }
            return false;
        }

        function resetResponseList(all) {
            // To reset the selected attribute on every ajax response,result hide and mouse out from list
            responseList.selected = null;
            if (all === true) {
            // To reset the list on search result hide
                responseList.indexList = null;
            }
        }

        $(searchInit.fieldSelector).on('blur', function () {
            if ($(this).val() === '') {
                $(this).val(searchInit.placeholder);
            }
            // use setTimeout to make sure submit event happens before blur event
            setTimeout(function () {
                $(searchInit.destinationSelector).hide();
            }, 250);
        });

        $(searchInit.fieldSelector).trigger('blur');

        $(searchInit.fieldSelector).on('focus', function () {
            if ($(this).val() === searchInit.placeholder) {
                $(this).val('');
            }
        });

        $(searchInit.fieldSelector).on('keydown', function (e) {

            var keyCode = e.keyCode || e.which;

            switch (keyCode) {

                case $.mage.constant.KEY_ESC:
                    resetResponseList(true);
                    $(searchInit.destinationSelector).hide();
                    break;
                case $.mage.constant.KEY_TAB:
                    $(searchInit.formSelector).trigger('submit');
                    break;
                case $.mage.constant.KEY_DOWN:
                    if (responseList.indexList) {
                        if (!responseList.selected) {
                            getFirstVisibleElement().addClass(searchInit.selectClass);
                            responseList.selected = getFirstVisibleElement();
                        }
                        else if (!getLastElement().hasClass(searchInit.selectClass)) {
                            responseList.selected = responseList.selected.removeClass(searchInit.selectClass).next().addClass(searchInit.selectClass);
                        } else {
                            responseList.selected.removeClass(searchInit.selectClass);
                            getFirstVisibleElement().addClass(searchInit.selectClass);
                            responseList.selected = getFirstVisibleElement();
                        }
                    }
                    break;
                case $.mage.constant.KEY_UP:
                    if (responseList.indexList !== null) {
                        if (!getFirstVisibleElement().hasClass(searchInit.selectClass)) {
                            responseList.selected = responseList.selected.removeClass(searchInit.selectClass).prev().addClass(searchInit.selectClass);

                        } else {
                            responseList.selected.removeClass(searchInit.selectClass);
                            getLastElement().addClass(searchInit.selectClass);
                            responseList.selected = getLastElement();
                        }
                    }
                    break;
                default:
                    return true;
            }
        });

        $(searchInit.formSelector).on('submit', function (e) {
            if ($(searchInit.fieldSelector).val() === searchInit.placeholder || $(searchInit.fieldSelector).val() === '') {
                e.preventDefault();
            }
            if (responseList.selected) {
                $(searchInit.fieldSelector).val(responseList.selected.attr('title'));
            }
        });

        $(searchInit.fieldSelector).on('input propertychange', function () {

            var searchField = $(this);
            var clonePostion = {
                position: 'absolute',
                left: searchField.offset().left,
                top: searchField.offset().top + searchField.outerHeight(),
                width: searchField.outerWidth()
            };

            if ($(this).val().length >= parseInt(searchInit.minSearchLength, 10)) {
                $.get(searchInit.url, {q: $(this).val()}, function (data) {
                    responseList.indexList = $(searchInit.destinationSelector).html(data)
                        .css(clonePostion)
                        .show()
                        .find(searchInit.responseFieldElements);
                    resetResponseList(false);
                    responseList.indexList.on('click',function () {
                        responseList.selected = $(this);
                        $(searchInit.formSelector).trigger('submit');
                    }).on('hover',function () {
                        responseList.indexList.removeClass(searchInit.selectClass);
                        $(this).addClass(searchInit.selectClass);
                        responseList.selected = $(this);
                    }).on('mouseout', function () {
                        if (!getLastElement() && getLastElement().hasClass(searchInit.selectClass)) {
                            $(this).removeClass(searchInit.selectClass);
                            resetResponseList(false);
                        }
                    });
                });
            } else {
                resetResponseList(true);
                $(searchInit.destinationSelector).hide();
            }
        });
    });
})(jQuery);

