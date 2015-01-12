/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "underscore",
    "Magento_Catalog/js/price-utils",
    "jquery/ui"
], function($,_, utils){
    "use strict";

    var globalOptions = {
        productId: null,
        priceHolderSelector: '.price-box', //data-role="priceBox"
        optionsSelector: '.product-custom-option',
        optionConfig: {},
        optionHandlers: {},
        controlContainer: 'dd'
    };

    $.widget('mage.priceOptions',{
        options: globalOptions,
        _create: createPriceOptions,
        _setOptions: setOptions
    });

    return $.mage.priceOptions;

    /**
     * Widget creating method.
     * Triggered once.
     */
    function createPriceOptions() {
        /*jshint validthis: true */
        var form = this.element;
        var options = $(this.options.optionsSelector, form);

        options.on('change', onOptionChanged.bind(this));
    }

    /**
     * Custom option change-event handler
     * @param event
     */
    function onOptionChanged(event) {
        /*jshint validthis: true */
        var changes;
        var option = $(event.target);
        var handler = this.options.optionHandlers[option.data('role')];
        option.data('optionContainer', option.closest(this.options.controlContainer));

        if(handler && handler instanceof Function) {
            changes = handler(option, this.options.optionConfig, this);
        } else {
            changes = defaultGetOptionValue(option, this.options.optionConfig);
        }

        $(this.options.priceHolderSelector).trigger('updatePrice', changes);
    }

    /**
     * Custom option preprocessor
     * @param element
     * @param  {Object} optionsConfig part of config
     * @return {Object}
     */
    function defaultGetOptionValue(element, optionsConfig) {
        var changes = {};
        var optionValue = element.val();
        var optionId = utils.findOptionId(element[0]);
        var optionName = element.prop('name');
        var optionType = element.prop('type');
        var optionConfig = optionsConfig[optionId];
        var optionHash = optionName;
        switch (optionType) {
            case 'text':
            case 'textarea':
                changes[optionHash] = optionValue ? optionConfig.prices : {};
                break;
            case 'radio':
            case 'select-one':
                changes[optionHash] = optionConfig[optionValue] && optionConfig[optionValue].prices || {};
                break;
            case 'select-multiple':
                _.each(optionConfig, function(row, optionValueCode) {
                    optionHash = optionName + '##' + optionValueCode;
                    changes[optionHash] = _.contains(optionValue, optionValueCode) ? row.prices : {};
                });
                break;
            case 'checkbox':
                optionHash = optionName + '##' + optionValue;
                changes[optionHash] = element.is(':checked') ? optionConfig[optionValue].prices : {};
                break;
            case 'file':
                // Checking for 'disable' property equal to checking DOMNode with id*="change-"
                changes[optionHash] = optionValue || element.prop('disabled') ? optionConfig.prices : {};
                break;
        }
        return changes;
    }

    /**
     * Custom behavior on getting options:
     * now widget able to deep merge accepted configuration with instance options.
     * @param  {Object}  options
     * @return {$.Widget}
     */
    function setOptions(options) {
        /*jshint validthis: true */
        $.extend(true, this.options, options);

        if('disabled' in options) {
            this._setOption('disabled', options.disabled);
        }
        return this;
    }
});