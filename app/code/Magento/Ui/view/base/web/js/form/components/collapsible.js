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