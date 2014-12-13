/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'underscore',
    'Magento_Ui/js/form/component'
], function(_, Component) {
    'use strict';

    var defaults = {
        collapsible:    false,
        opened:         true
    };

    var __super__ = Component.prototype;

    return Component.extend({

        /**
         * Merges 'defaults' to instance, calls 'initialize' of parent
         */
        initialize: function() {
            _.extend(this, defaults);

            __super__.initialize.apply(this, arguments);
        },

        /**
         * Initializes 'opened' observable, calls 'initObservable' of parent
         * 
         * @return {Object} - reference to instance
         */
        initObservable: function(){
            __super__.initObservable.apply(this, arguments);

            this.observe('opened');

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