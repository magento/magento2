/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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