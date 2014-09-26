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
    'underscore'
], function(_) {
    'use strict';

    /**
     * Analogue of Backbone.extend function.
     * @param  {Object} protoProps - object, that describes the prototype of
     * created constructor.
     * @return {Function} - new constructor
     */
    function extend( protoProps ){
        var parent = this,
            child,
            args,
            hasConstructor;

        protoProps      = protoProps || {};
        hasConstructor  = protoProps.hasOwnProperty('constructor');

        child = hasConstructor ?
            protoProps.constructor :
            function() {
                return parent.apply(this, arguments);
            };

        child.prototype = Object.create( parent.prototype );
        child.prototype.constructor = child;

        args = [child.prototype];

        args.push.apply(args, arguments);

        _.extend.apply(_, args);

        child.extend = extend;
        child.__super__ = parent.prototype;

        return child;
    }

    /**
     * Constructor, which calls initialize with passed arguments.
     */
    function Class() {
        this.initialize.apply(this, arguments);
    }

    Class.prototype.initialize = function(){};

    Class.extend = extend;

    return Class;
});