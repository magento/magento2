/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'underscore',
    'mage/utils',
    'Magento_Ui/js/form/component',
    'Magento_Ui/js/lib/validation/validator'
], function (_, utils, Component, validator) {
    'use strict';

    var defaults = {
        hidden:             false,
        preview:            '',
        focused:            false,
        tooltip:            null,
        required:           false,
        disabled:           false,
        tmpPath:            'ui/form/element/',
        tooltipTpl:         'ui/form/element/helper/tooltip',
        input_type:         'input',
        placeholder:        null,
        noticeid:           null,
        description:        '',
        label:              '',
        error:              '',
        notice:             null
    };

    var __super__ = Component.prototype;

    return Component.extend({

        /**
         * Invokes initialize method of parent class, contains initialization
         *     logic
         *     
         * @param {Object} config - form element configuration
         */
        initialize: function () {
            _.extend(this, defaults);

            _.bindAll(this, 'onUpdate', 'reset');

            __super__.initialize.apply(this, arguments);

            this.setHidden(this.hidden())
                .store(this.value());
        },

        /**
         * Initializes observable properties of instance
         * 
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            var value = this.getInititalValue(), 
                rules;

            __super__.initObservable.apply(this, arguments);

            rules = this.validation = this.validation || {};

            this.initialValue = value;

            this.observe('error disabled focused preview hidden')
                .observe({
                    'value':    value,
                    'required': !!rules['required-entry']
                });

            return this;
        },

        /**
         * Initializes regular properties of instance.
         * 
         * @returns {Abstract} Chainable.
         */
        initProperties: function () {
            __super__.initProperties.apply(this, arguments);

            _.extend(this, {
                'uid':        utils.uniqueid(),
                'inputName':  utils.serializeName(this.dataScope)
            });

            _.defaults(this, {
                'template': this.tmpPath + this.input_type
            });

            return this;
        },

        /**
         * Initializes instance's listeners.
         * 
         * @returns {Abstract} Chainable.
         */
        initListeners: function(){
            var provider  = this.provider,
                data      = provider.data;

            __super__.initListeners.apply(this, arguments);

            data.on('reset', this.reset, this.name);
            
            this.value.subscribe(this.onUpdate);

            return this;
        },

        /**
         * Gets initial value of element
         * 
         * @returns {*} Elements' value.
         */
        getInititalValue: function(){
            var data    = this.provider.data,
                value   = data.get(this.dataScope);

            if(_.isUndefined(value) || _.isNull(value)){
                value = '';
            }

            return value;
        },

        /**
         * Defines notice id for the element.
         * 
         * @returns {String} Notice id.
         */
        getNoticeId: function () {
            return 'notice-' + this.uid;
        },

        /**
         * Sets value to preview observable
         * 
         * @returns {Abstract} Chainable.
         */
        setPreview: function(value){
            this.preview(this.hidden() ? '' : value);

            return this;
        },

        /**
         * Returnes unwrapped preview observable.
         * 
         * @returns {String} Value of the preview observable.
         */
        getPreview: function(){
            return this.preview();
        },

        /**
         * Calls 'setHidden' passing true to it.
         */
        hide: function(){
            this.setHidden(true);

            return this;
        },

        /**
         * Calls 'setHidden' passing false to it.
         */
        show: function(){
            this.setHidden(false);

            return this;
        },

        /**
         * Sets 'value' as 'hidden' propertie's value, triggers 'toggle' event,
         * sets instance's hidden identifier in params storage based on
         * 'value'.
         * 
         * @returns {Abstract} Chainable.
         */
        setHidden: function(isHidden){
            var params = this.provider.params;

            this.hidden(isHidden);
    
            this.setPreview(this.value())
                .trigger('toggle', isHidden);

            params.set(this.name + '.hidden', isHidden);

            return this;
        },

        /**
         * Checkes if element has addons
         * 
         * @returns {Boolean}
         */
        hasAddons: function () {
            return this.addbefore || this.addafter;
        },

        /**
         * Defines if value has changed.
         *
         * @returns {Boolean}
         */
        hasChanged: function(){
            var notEqual = this.value() != this.initialValue;

            return this.hidden() ? false : notEqual;
        },

        /**
         * Stores element's value to registry by element's path value
         * @param  {*} value - current value of form element
         * @returns {Abstract} Chainable.
         */
        store: function (value) {
            var data = this.provider.data;

            data.set(this.dataScope, value);

            return this;
        },

        /**
         * Sets value observable to initialValue property.
         */
        reset: function(){
            this.value(this.initialValue);
        },

        /**
         * Validates itself by it's validation rules using validator object.
         * If validation of a rule did not pass, writes it's message to
         * 'error' observable property.
         *     
         * @returns {Boolean} True, if element is invalid.
         */
        validate: function () {
            var value   = this.value(),
                msg     = validator(this.validation, value),
                isValid = this.hidden() || !msg;

            this.error(msg);

            return {
                valid:  isValid,
                target: this
            };
        },

        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function (value) {            
            this.store(value)
                .setPreview(value)
                .trigger('update', this.hasChanged());

            this.validate();
        },
    });
});