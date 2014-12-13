/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'underscore',
    '../component'
], function(_, Component) {
    'use strict';

    var defaults = {
        uniqueProp:     'active',
        active:         false,
        wasActivated:   false
    };

    var __super__ = Component.prototype;

    return Component.extend({

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function() {
            _.extend(this, defaults);

            __super__.initialize.apply(this, arguments);

            this.setUnique();
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function() {
            __super__.initObservable.apply(this, arguments);

            this.observe('active wasActivated');

            return this;
        },
        
        onUniqueUpdate: function(name){
            var active = name === this.name;

            __super__.onUniqueUpdate.apply(this, arguments);

            this.trigger('active', active);
        },
        
        /**
         * Sets active property to true, then invokes pushParams method.
         */
        activate: function(){
            this.active(true);
            this.wasActivated(true);

            this.setUnique();
        }
    });
});