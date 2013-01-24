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
 * @category    mage
 * @package     mage
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint eqnull:true browser:true jquery:true*/
/*global head:true */
(function($) {
    "use strict";
    /**
     * Main namespace for Magento extansions
     * @type {Object}
     */
    $.mage = {};
})(jQuery);

/**
 * Plugin mage and group of heplers for it
 */
(function($) {
    "use strict";
    /**
     * Plugin mage, initialize components on elements
     * @param {string} name - component name
     * @param {}
     * @return {Object}
     */
    $.fn.mage = function() {
        var name = arguments[0],
            args = Array.prototype.slice.call(arguments, 1);
        return this.each(function(){
            var inits = _getInitData(this);
            if (name) {
                inits[name] = args;
            }
            $.each(inits, $.proxy(_initComponent, this));
        });
    };

    /**
     * Storage of declared resources
     * @type {Object}
     * @private
     */
    var _resources = {};

    /**
     * Execute initialization callback when all resources are loaded
     * @param {Array} args - list of resources
     * @param {Function} handler - initialization callback
     * @private
     */
    function _onload(args, handler) {
        args.push(handler);
        head.js.apply(head, args);
    }

    /**
     * Run initialization of a component
     * @param {Object} init - setting for a component in format
     *      {name: {string}[, options: {Object}][, args: {Array}][, resources: {Array}]}
     * @private
     */
    function _initComponent(name, args) {
        /*jshint validthis: true */
        // create a complete copy of arguments
        args = $.map($.makeArray(args), function(arg) {
            return $.isArray(arg) ? [arg.slice()] :
                $.isPlainObject(arg) ? $.extend(true, {}, arg) : arg;
        });
        var init = {
            name: name,
            args: args,
            resources: (_resources[name] || []).slice()
        };
        // Through event-listener 3-rd party developer can modify options and list of resources
        $($.mage).trigger($.Event(name + 'init', {target: this}), init);
        // Component name was deleted, so there's nothing else to do
        if (!init.name) {
            return;
        }
        // Build an initialization handler
        var handler = $.proxy(function() {
            this[init.name].apply(this, init.args);
        }, $(this));
        if (init.resources.length) {
            _onload(init.resources, handler);
        } else {
            handler();
        }
    }

    /**
     * Define init-data from an element,
     *     if JSON is not well-formed then evaluate init-data by manually
     * @param {Element} elem
     * @return {Object}
     * @private
     */
    function _getInitData(elem) {
        /*jshint evil:true*/
        var inits = $(elem).data('mage-init') || {};
        // in case it's not well-formed JSON inside data attribute, evaluate it manually
        if (typeof inits === 'string') {
            try {
                inits = eval('(' + inits + ')');
            } catch (e) {
                inits = {};
            }
        }
        return inits;
    }

    /**
     * Find all elements with data attribute and initialize them
     * @param {Element} elem - context 
     * @private
     */
    function _init(elem) {
        $(elem).add('[data-mage-init]', elem).mage();
    }

    $.extend($.mage, {
        /**
         * Handle all components declared via data attribute
         * @return {Object} $.mage
         */
        init: function() {
            _init(document);
            /**
             * Init components inside of dynamically updated elements
             */
            $('body').on('contentUpdated', function(e) {
                _init(e.target);
            });
            return this;
        },

        /**
         * Declare a new component based on already declared one in the mage widget
         * @param {string} component - name of a new component
         *      (can be the same as a name of super component)
         * @param {string} from - name of super component
         * @param {(undefined|Object|Array)} resources - list of resources
         * @return {Object} $.mage
         */
        extend: function(component, from, resources) {
            resources = $.merge(
                (_resources[from] || []).slice(),
                $.makeArray(resources)
            );
            this.component(component, resources);
            return this;
        },

        /**
         * Declare a new component or several components at a time in the mage widget
         * @param {(string|Object)} component - name of component
         *      or several componets with lists of required resources
         *      {component1: {Array}, component2: {Array}}
         * @param {(string|Array)} resources - URL of one resource or list of URLs
         * @return {Object} $.mage
         */
        component: function(component) {
            if ($.isPlainObject(component)) {
                $.extend(_resources, component);
            } else if (typeof component === 'string' && arguments[1]) {
                _resources[component] = $.makeArray(arguments[1]);
            }
            return this;
        },

        /**
         * Helper allows easily bind handler with component's initialisation
         * @param {string} component - name of a component
         *      which initialization shold be customized
         * @param {(string|Function)} selector [optional]- filter of component's elements
         *      or a handler function if selector is not defined
         * @param {Function} handler - handler function
         * @return {Object} $.mage
         */
        onInit: function(component, selector, handler) {
            if (!handler) {
                handler = selector;
                selector = null;
            }
            $(this).on(component + 'init', function(e, init) {
                if (!selector || $(e.target).is(selector)) {
                    handler.apply(init, init.args);
                }
            });
            return this;
        },

        /**
         * Load all resource for certain component or several components
         * @param {string} component - name of a component
         *     (several components may be passed also as separate arguments)
         * @return {Object} $.mage
         */
        load: function() {
            $.each(arguments, function(i, component) {
                if (_resources[component] && _resources[component].length) {
                    head.js.apply(head, _resources[component]);
                }
            });
            return this;
        }
    });
})(jQuery);

(function($) {
    "use strict";
    $.extend(true, $, {
        mage: {
            constant: {
                KEY_BACKSPACE: 8,
                KEY_TAB: 9,
                KEY_RETURN: 13,
                KEY_ESC: 27,
                KEY_LEFT: 37,
                KEY_UP: 38,
                KEY_RIGHT: 39,
                KEY_DOWN: 40,
                KEY_DELETE: 46,
                KEY_HOME: 36,
                KEY_END: 35,
                KEY_PAGEUP: 33,
                KEY_PAGEDOWN: 34
            }
        }
    });
})(jQuery);
