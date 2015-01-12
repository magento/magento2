/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'Magento_Ui/js/form/component'
], function(Component) {
    'use strict';

    return Component.extend({
        defaults: {
            collapsible:    false,
            opened:         true
        },

        /**
         * Initializes 'opened' observable, calls 'initObservable' of parent
         * 
         * @return {Object} - reference to instance
         */
        initObservable: function(){
            this._super()
                .observe('opened');

            return this;
        },

        /**
         * Toggles 'active' observable, triggers 'active' event
         * 
         * @return {Object} - reference to instance
         */
        toggle: function() {
            var opened = this.opened,
                active = opened(!opened());

            this.trigger('active', active);

            return this;
        },

        /**
         * Invokes 'toggle' method if instance has 'collapsible' property set to true
         */
        onClick: function(){
            if(this.collapsible){
                this.toggle();
            }
        }
    });
});