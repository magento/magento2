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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint eqnull:true browser:true jquery:true*/
/*global head:true console:true*/
(function($) {
    "use strict";
    /**
     * Store developer mode flag value
     * @type {boolean}
     * @private
     */
    var _isDevMode = false;

    /**
     * Main namespace for Magento extensions
     * @type {Object}
     */
    $.mage = {
        /**
         * Setter and getter for developer mode flag
         * @param {(undefined|boolean)} flag
         * @return {boolean}
         */
        isDevMode: function(flag) {
            if (typeof flag !== 'undefined') {
                _isDevMode = !!flag;
            }
            return _isDevMode && typeof console !== 'undefined';
        }
    };
})(jQuery);

/**
 * Plugin mage and group of helpers for it
 */
(function($) {
    "use strict";
    /**
     * Plugin mage, initialize components on elements
     * @param {string} name - component name
     * @param {}
     * @return {Object}
     */
    $.fn.mage = function(name) {
        var args = Array.prototype.slice.call(arguments, 1);
        return this.each(function(){
            var inits = $(this).data('mage-init') || {};
            if (name) {
                inits[name] = args;
            }
            $.each(inits, $.proxy(_initComponent, this));
        });
    };

    window.onerror = function (message) {
        if ($.mage.isDevMode()) {
            $('[data-container-for=messages]').append(
                '<div class="messages"><div class="message error" data-role="js-error"><div>' + message + '</div></div></div>'
            );
        }
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
     * @param {(Function|undefined)} handler - initialization callback
     * @private
     */
    function _onload(args, handler) {
        args = $.grep(args, function(resource) {
            var script = $('script[src="' + resource + '"]');
            return !script.length || typeof script[0].onload === 'function';
        });

        if (typeof handler === 'function' && args.length) {
            args.push(handler);
        }

        if (args.length) {
            head.js.apply(head, args);
        } else if (typeof handler === 'function') {
            handler();
        }
    }

    /**
     * Run initialization of a component
     * @param {String} name
     * @param {Array} args
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
            args: args.length > 0 ? args : [{}],
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
            if (typeof this[init.name] === 'function') {
                this[init.name].apply(this, init.args);
            } else if ($.mage.isDevMode()) {
                console.error('Cannot initialize components "' + init.name + '"');
            }
        }, $(this));
        _onload(init.resources, handler);
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
         *      or several components with lists of required resources
         *      {component1: {Array}, component2: {Array}}
         * @param {(string|Array)} component - URL of one resource or list of URLs
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
         *      which initialization should be customized
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
         * @param {String} component - name of a component
         *     (several components may be passed also as separate arguments)
         * @return {Object} $.mage
         */
        load: function() {
            $.each(arguments, function(i, component) {
                if (_resources[component] && _resources[component].length) {
                    _onload(_resources[component]);
                }
            });
            return this;
        }
    });
})(jQuery);

(function($, window) {
    "use strict";
    $.extend($.mage, {
            /**
             * Method handling redirects and page refresh
             * @param {String} url - redirect URL
             * @param {(undefined|String)} type - 'assign', 'reload', 'replace'
             * @param {(undefined|Number)} timeout - timeout in milliseconds before processing the redirect or reload
             * @param {(undefined|Boolean)} forced - true|false used for 'reload' only
             */
            redirect: function(url, type, timeout, forced) {
                forced = forced ? true : false;
                timeout = timeout ? timeout : 0;
                type = type ? type : "assign";
                var _redirect = function() {
                    window.location[type](type === 'reload' ? forced : url);
                };
                if (timeout) {
                    setTimeout(_redirect, timeout);
                } else {
                    _redirect();
                }
            }
    });
})(jQuery, window);
