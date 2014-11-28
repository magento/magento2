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