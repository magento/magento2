/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    './collapsible',
    'Magento_Ui/js/lib/spinner'
], function(_, Collapsible, loader) {
    'use strict';
   
    return Collapsible.extend({

        /**
         * Invokes initElement method of parent class, calls 'initActivation' method
         * passing element to it.
         * @param {Object} elem
         * @returns {Object} - reference to instance
         */
        initElement: function(elem){
            this._super()
                .initActivation(elem)
                .hideLoader();

            return this;
        },

        /**
         * Binds 'onValidate' method as handler for data storage's 'validate' event
         * 
         * @return {Object} - reference to instance
         */
        initListeners: function(){
            var data    = this.provider.data,
                handler = this.onValidate.bind(this);

            this._super();

            data.on('validate', handler, this.name);
            
            return this;
        },

        /**
         * Activates element if one is first or if one has 'active' propert
         * set to true.
         *
         * @param  {Object} elem
         * @returns {Object} - reference to instance
         */
        initActivation: function(elem){
            var elems   = this.elems(),
                isFirst = !elems.indexOf(elem);

            if(isFirst || elem.active()){
                elem.activate();
            }

            return this;
        },

        hideLoader: function () {
            loader.get(this.name).hide();
        },

        /**
         * Delegates 'validate' method on element, then reads 'invalid' property
         * of params storage, and if defined, activates element, sets 
         * 'allValid' property of instance to false and sets invalid's
         * 'focused' property to true.
         *
         * @param {Object} elem
         */
        validate: function(elem){
            var params  = this.provider.params,
                result  = elem.delegate('validate'),
                invalid = false;

            _.some(result, function(item){
                return !item.valid && (invalid = item.target);
            });

            if (invalid && !params.get('invalid')) {
                params.set('invalid', invalid);

                elem.activate();
                invalid.focused(true);
            }
        },

        /**
         * Sets 'allValid' property of instance to true, then calls 'validate' method
         * of instance for each element 
         */
        onValidate: function(){
            var elems;

            elems = this.elems.sortBy(function(elem){
                return !elem.active();
            });            

            elems.each(this.validate, this);
        }
    });
});