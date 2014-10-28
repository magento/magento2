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
 /*global require:true*/
(function(factory){
    if( require && define && define.amd ){
        factory( require );
    }
}(function(require){

    var context         = require.s.contexts._,
        completeLoad    = context.completeLoad,
        registry        = context.registry,
        resolver,
        listeners;

    listeners = {};

    /**
     * Method that triggers all of the attached 'onAllResolved' callbacks.
     * @protected
     */
    function trigger(){
        var namespace,
            handlers,
            hi,
            hl;
        
        resolver.resolved = true;

        for( namespace in listeners ){
            handlers = listeners[ namespace ];
            hl  = handlers.length;

            for( hi = 0; hi < hl; hi++ ){
                handlers[ hi ]();
            }

            handlers.splice( 0, hl );
        }

    }

    resolver = {
        
        /**
         * Checks wethre all of the current dependencies are resolved.
         * returns {Boolean}
         */
        isResolved: function(){
            return !Object.keys( registry ).length;
        },


        /**
         * Attaches event handler for the 'onAllResolved' event.
         * @param {String} [namespace = _default] - Namespace of the handler.
         * @param {Function} callback - Events' callback function.
         */
        on: function( namespace, callback ){
            var handlers;

            if( arguments.length === 1 && typeof namespace === 'function' ){
                callback = namespace;
                namespace = '_default';
            }
            
            if( this.isResolved() ){
                callback();
            }
            else{
                handlers = listeners[namespace] = listeners[namespace] || [];

                handlers.push( callback );
            }

            return resolver;
        },

        /**
         * Checks for the attached listeners.
         * @praram {String} [namespace = _default] - Namespace of the handler.
         * @return {Boolean}
         */
        hasListeners: function( namespace ){  
            var handlers;

            if( typeof namespace === 'undefined' ){
                namespace = '_default';
            }

            handlers = listeners[namespace];

            return handlers && handlers.length;
        }
    };


    /**
     * Inner requirejs's context method that fires whenever script has been loaded.
     */
    context.completeLoad = function(){
        completeLoad.apply(context, arguments);
        
        if( resolver.isResolved() ){
            trigger();
        }
    };

    require.resolver = resolver;
}));
