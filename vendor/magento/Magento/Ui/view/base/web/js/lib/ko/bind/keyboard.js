/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
	'ko',
	'underscore'
], function(ko, _) {
	'use strict';

	var on = ko.utils.registerEventHandler.bind(ko.utils);

	ko.bindingHandlers.keyboard = {

		/**
         * Attaches keypress handlers to element
         * @param {HTMLElement} el - Element, that binding is applied to
         * @param {Function} valueAccessor - Function that returns value, passed to binding
         * @param  {Object} allBindings - all bindings object
         * @param  {Object} viewModel - reference to viewmodel
         */
		init: function(element, valueAccessor, allBindings, viewModel) {
			var defs = valueAccessor();

			if (typeof defs === 'object') {
				_.each(defs, function (callback, keyCode) {
					on(element, 'keypress', function(event) {
						if (event.keyCode == keyCode) {
							callback.call(viewModel);
						}
					});	
				});
			}
		}
	};
});