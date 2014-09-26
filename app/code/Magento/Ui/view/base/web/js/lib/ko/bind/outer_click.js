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
/** Creates outerClick binding and registers in to ko.bindingHandlers object */
define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    ko.bindingHandlers.outerClick = {

        /**
         * Attaches click handler to document
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         * @param  {Object} allBindings - all bindings object
         * @param  {Object} viewModel - reference to viewmodel
         */
        init: function (element, valueAccessor, allBindings, viewModel) {
            var callback = valueAccessor();

            callback = callback.bind(viewModel);

            $(document).on('click', callback);

            ko.utils.domNodeDisposal.addDisposeCallback(element, function () {
                $(document).off('click', callback);
            });
        }
    }
});