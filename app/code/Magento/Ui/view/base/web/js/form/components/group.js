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
    '../component',
    'mage/utils'
], function(_, Component, utils) {
    'use strict';

    var defaults = {
        hidden:         false,
        label:          '',
        required:       false,
        template:       'ui/group/group',
        fieldTemplate:  'ui/group/field',
        breakLine:      true
    };

    var __super__ = Component.prototype;

    function extractData(container, field){
        var data,
            value;

        container.some(function(item){
            value = item[field];

            if(_.isFunction(value)){
                value = value();
            }

            return !item.hidden() && (data = value);
        });

        return data || '';
    }

    return Component.extend({

        /**
         * Extends this with defaults and config.
         * Then calls initObservable, iniListenes and extractData methods.
         * 
         * @param  {Object} config
         */
        initialize: function() {
            _.extend(this, defaults);
            
            _.bindAll(this, 'toggle');

            __super__.initialize.apply(this, arguments);
        },

        /**
         * Calls initObservable of parent class.
         * Defines observable properties of instance.
         * 
         * @return {Object} - reference to instance
         */
        initObservable: function(){
            __super__.initObservable.apply(this, arguments);

            return this.observe('hidden label required');
        },

        /**
         * Assignes onUpdate callback to update event of incoming element.
         * Calls extractData method.
         * @param  {Object} element
         * @return {Object} - reference to instance
         */
        initElement: function(elem){
            __super__.initElement.apply(this, arguments);

            elem.on({
                'toggle': this.toggle
            });

            this.extractData();

            return this;
        },

        /**
         * Extracts label and required properties from child elements
         * 
         * @return {Object} - reference to instance
         */
        extractData: function(){
            var elems = this.elems();

            this.label(extractData(elems, 'label'));
            this.required(extractData(elems, 'required'));

            return this;
        },

        /**
         * Sets incoming value to hidden observable, calls extractData method
         * 
         * @param  {Boolean} value
         */
        toggle: function(value){
            this.extractData()
                .hidden(value);
        },

        /**
         * Defines if group has only one element.
         * @return {Boolean}
         */
        isSingle: function () {
            return this.elems.getLength() === 1;
        },

        /**
         * Defines if group has multiple elements.
         * @return {Boolean}
         */
        isMultiple: function () {
            return this.elems.getLength() > 1;
        }
    });
});