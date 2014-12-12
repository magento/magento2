/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'underscore',
    './tab'
], function(_, Tab) {
    'use strict';

    var defaults = {
        uniqueNs:   'activeArea',
        template:   'ui/area',
        changed:    false,
        loading:    false
    };

    var __super__ = Tab.prototype;

    return Tab.extend({

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function() {
            _.extend(this, defaults);

            _.bindAll(this, 'onChildrenUpdate', 'onContentLoading', 'onContentLoaded');

            __super__.initialize.apply(this, arguments);
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function() {
            __super__.initObservable.apply(this, arguments);

            this.observe('changed loading');

            return this;
        },

        /**
         * Calls parent's initElement method.
         * Assignes callbacks on various events of incoming element.
         * @param  {Object} elem
         * @return {Object} - reference to instance
         */
        initElement: function(elem){
            __super__.initElement.apply(this, arguments);

            elem.on({
                'update':   this.onChildrenUpdate,
                'loading':  this.onContentLoading,
                'loaded':   this.onContentLoaded
            });

            return this;
        },

        /**
         * Is being invoked on children update.
         * Sets changed property to one incoming.
         * Invokes setActive method if settings
         * contain makeVisible property set to true.
         * 
         * @param  {Boolean} changed
         */
        onChildrenUpdate: function(hasChanged){
            if(!hasChanged){
                hasChanged = _.some(this.delegate('hasChanged'));
            }

            this.changed(hasChanged);
        },

        /**
         * Callback that sets loading property to true.
         */
        onContentLoading: function(){
            this.loading(true);
        },

        /**
         * Callback that sets loading property to true.
         */
        onContentLoaded: function(){
            this.loading(false);
        }
    });
});