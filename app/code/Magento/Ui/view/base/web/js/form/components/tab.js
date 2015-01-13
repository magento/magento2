/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    '../component'
], function(Component) {
    'use strict';

    return Component.extend({
        defaults: {
            uniqueProp:     'active',
            active:         false,
            wasActivated:   false
        },

        /**
         * Extends instance with defaults. Invokes parent initialize method.
         * Calls initListeners and pushParams methods.
         */
        initialize: function() {
            this._super()
                .setUnique();
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * @return {Object} - reference to instance
         */
        initObservable: function() {
            this._super()
                .observe('active wasActivated');

            return this;
        },
        
        onUniqueUpdate: function(name){
            var active = name === this.name;

            this._super();

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