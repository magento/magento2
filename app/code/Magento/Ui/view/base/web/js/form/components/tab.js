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