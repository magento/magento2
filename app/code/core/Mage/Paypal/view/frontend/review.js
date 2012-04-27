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
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Controller of order review form that may select shipping method
 */
OrderReviewController = Class.create();
OrderReviewController.prototype = {
    _canSubmitOrder : false,
    _pleaseWait : false,
    shippingSelect : false,
    reloadByShippingSelect : false,
    _copyElement : false,
    onSubmitShippingSuccess : false,
    shippingMethodsUpdateUrl : false,
    _updateShippingMethods: false,
    _ubpdateOrderButton : false,
    shippingMethodsContainer: false,
    _submitUpdateOrderUrl : false,
    _itemsGrid : false,

    /**
     * Add listeners to provided objects, if any
     * @param orderForm - form of the order submission
     * @param orderFormSubmit - element for the order form submission (optional)
     * @param shippingSelect - dropdown with available shipping methods (optional)
     * @param shippingSubmitForm - form of shipping method submission (optional, requires shippingSelect)
     * @param shippingResultId - element where to update results of shipping ajax submission (optional, requires shippingSubmitForm)
     */
    initialize : function(orderForm, orderFormSubmit, shippingSelect, shippingSubmitForm, shippingResultId, shippingSubmit)
    {
        if (!orderForm) {
            return;
        }
        this.form = orderForm;
        if (orderFormSubmit) {
            this.formSubmit = orderFormSubmit;
            Event.observe(orderFormSubmit, 'click', this._submitOrder.bind(this));
        }

        if (shippingSubmitForm) {
            this.reloadByShippingSelect = true;
            if (shippingSubmitForm && shippingSelect) {
                this.shippingSelect = shippingSelect;
                Event.observe(
                    shippingSelect,
                    'change',
                    this._submitShipping.bindAsEventListener(this, shippingSubmitForm.action, shippingResultId)
                );
                this._updateOrderSubmit(false);
            } else {
                this._canSubmitOrder = true;
            }
        } else {
            Form.getElements(this.form).each(this._bindElementChange, this);

            if (shippingSelect && $(shippingSelect)) {
                this.shippingSelect = $(shippingSelect).id;
                this.shippingMethodsContainer = $(this.shippingSelect).up();
            } else {
                this.shippingSelect = shippingSelect;
            }
            this._updateOrderSubmit(false);
        }
    },

    /**
     * Register element that should show up when ajax request is in progress
     * @param element
     */
    addPleaseWait : function(element)
    {
        if (element) {
            this._pleaseWait = element;
        }
    },

    /**
     * Dispatch an ajax request of shipping method submission
     * @param event
     * @param url - url where to submit shipping method
     * @param resultId - id of element to be updated
     */
    _submitShipping : function(event, url, resultId)
    {
        if (this.shippingSelect && url && resultId) {
            this._updateOrderSubmit(true);
            if (this._pleaseWait) {
                this._pleaseWait.show();
            }
            if ('' != this.shippingSelect.value) {
                new Ajax.Updater(resultId, url, {
                    parameters: {isAjax:true, shipping_method:this.shippingSelect.value},
                    onComplete: function() {
                        if (this._pleaseWait) {
                            this._pleaseWait.hide();
                        }
                    }.bind(this),
                    onSuccess: this._onSubmitShippingSuccess.bind(this),
                    evalScripts: true
                });
            }
        }
    },

    /**
     * Set event observer to Update Order button
     * @param element
     * @param url - url to submit on Update button
     * @param resultId - id of element to be updated
     */
    setUpdateButton : function(element, url, resultId)
    {
        if (element) {
            this._ubpdateOrderButton = element;
            this._submitUpdateOrderUrl = url;
            this._itemsGrid = resultId;
            Event.observe(element, 'click', this._submitUpdateOrder.bindAsEventListener(this, url, resultId));
            if(this.shippingSelect) {
                this._updateShipping();
            }
            this._updateOrderSubmit(!this._validateForm());
            this.formValidator.reset();
            this._clearValidation('');
        }
    },

    /**
     * Set event observer to copy data from shipping address to billing
     * @param element
     */
    setCopyElement : function(element)
    {
        if (element) {
            this._copyElement = element;
            Event.observe(element, 'click', this._copyShippingToBilling.bind(this));
            this._copyShippingToBilling();
        }
    },

    /**
     * Set observers to Shipping Address elements
     * @param element Container of Shipping Address elements
     */
    setShippingAddressContainer: function(element)
    {
        if (element) {
            Form.getElements(element).each(function(input) {
                if (input.type.toLowerCase() == 'radio' || input.type.toLowerCase() == 'checkbox') {
                    Event.observe(input, 'click', this._onShippingChange.bindAsEventListener(this));
                } else {
                    Event.observe(input, 'change', this._onShippingChange.bindAsEventListener(this));
                }
            }, this);
        }
    },

    /**
     * Sets Container element of Shipping Method
     * @param element Container element of Shipping Method
     */
    setShippingMethodContainer: function(element)
    {
        if (element) {
            this.shippingMethodsContainer = element;
        }
    },

    /**
     * Copy element value from shipping to billing address
     * @param el
     */
    _copyElementValue: function(el)
    {
        var newId = el.id.replace('shipping:','billing:');
        if (newId && $(newId) && $(newId).type != 'hidden') {
            $(newId).value = el.value;
            $(newId).setAttribute('readOnly', 'readonly');
            $(newId).addClassName('local-validation');
            $(newId).setStyle({opacity:.5});
            $(newId).disable();
        }
    },

    /**
     * Copy data from shipping address to billing
     */
    _copyShippingToBilling : function (event)
    {
        if (!this._copyElement) {
            return;
        }
        if (this._copyElement.checked) {
            this._copyElementValue($('shipping:country_id'));
            billingRegionUpdater.update();
            $$('[id^="shipping:"]').each(this._copyElementValue);
            this._clearValidation('billing');
        } else {
            $$('[id^="billing:"]').invoke('enable');
            $$('[id^="billing:"]').each(function(el){el.removeAttribute("readOnly");});
            $$('[id^="billing:"]').invoke('removeClassName', 'local-validation');
            $$('[id^="billing:"]').invoke('setStyle', {opacity:1});
        }
        if (event) {
            this._updateOrderSubmit(true);
        }
    },

    /**
     * Dispatch an ajax request of Update Order submission
     * @param url - url where to submit shipping method
     * @param resultId - id of element to be updated
     */
    _submitUpdateOrder : function(event, url, resultId)
    {
        this._copyShippingToBilling();
        if (url && resultId && this._validateForm()) {
            if (this._copyElement && this._copyElement.checked) {
                this._clearValidation('billing');
            }
            this._updateOrderSubmit(true);
            if (this._pleaseWait) {
                this._pleaseWait.show();
            }
            this._toggleButton(this._ubpdateOrderButton, true);

            arr = $$('[id^="billing:"]').invoke('enable');
            var formData = this.form.serialize(true);
            if (this._copyElement.checked) {
                $$('[id^="billing:"]').invoke('disable');
                this._copyElement.enable();
            }
            formData.isAjax = true;
            new Ajax.Updater(resultId, url, {
                parameters: formData,
                onComplete: function() {
                    if (this._pleaseWait && !this._updateShippingMethods) {
                        this._pleaseWait.hide();
                    }
                    this._toggleButton(this._ubpdateOrderButton, false);
                }.bind(this),
                onSuccess: this._updateShippingMethodsElement.bind(this),
                evalScripts: true
            });
        } else {
            if (this._copyElement && this._copyElement.checked) {
                this._clearValidation('billing');
            }
        }
    },

    /**
     * Update Shipping Methods Element from server
     */
    _updateShippingMethodsElement : function (){
        if (this._updateShippingMethods ) {
            new Ajax.Updater($(this.shippingMethodsContainer).up(), this.shippingMethodsUpdateUrl, {
                onComplete: this._updateShipping.bind(this),
                onSuccess: this._onSubmitShippingSuccess.bind(this),
                evalScripts: false
            });
        } else {
            this._onSubmitShippingSuccess();
        }
    },

    /**
     * Update Shipping Method select element and bind events
     */
    _updateShipping : function () {
        if ($(this.shippingSelect)) {
            $(this.shippingSelect).enable();
            Event.stopObserving($(this.shippingSelect), 'change');

            this._bindElementChange($(this.shippingSelect));
            Event.observe(
                $(this.shippingSelect),
                'change',
                this._submitUpdateOrder.bindAsEventListener(this, this._submitUpdateOrderUrl, this._itemsGrid)
            );

            $(this.shippingSelect + '_update').hide();
            $(this.shippingSelect).show();
        }
        this._updateShippingMethods = false;
        if (this._pleaseWait) {
            this._pleaseWait.hide();
        }
    },

    /**
     * Validate Order form
     */
    _validateForm : function()
    {
        if (!this.form) {
            return false;
        }
        if (!this.formValidator) {
            this.formValidator = new Validation(this.form);
        }

        return this.formValidator.validate();
    },

    /**
     * Actions on change Shipping Address data
     * @param event
     */
    _onShippingChange : function(event){
        var element = Event.element(event);
        if (element != $(this.shippingSelect) && !($(this.shippingSelect) && $(this.shippingSelect).disabled)) {
            if ($(this.shippingSelect)) {
                $(this.shippingSelect).disable();
                $(this.shippingSelect).hide();
                if ($('advice-required-entry-' + this.shippingSelect)) {
                    $('advice-required-entry-' + this.shippingSelect).hide();
                }
            }
            if (this.shippingMethodsContainer && $(this.shippingMethodsContainer)) {
                $(this.shippingMethodsContainer).hide();
            }

            if (this.shippingSelect && $(this.shippingSelect + '_update')) {
                $(this.shippingSelect + '_update').show();
            }
            this._updateShippingMethods = true;
        }
    },

    /**
     * Bind onChange event listener to elements for update Submit Order button state
     * @param input
     */
    _bindElementChange : function(input){
        Event.observe(input, 'change', this._onElementChange.bindAsEventListener(this))
    },

    /**
     * Disable Submit Order button
     */
    _onElementChange : function(){
        this._updateOrderSubmit(true);
    },

    /**
     * Clear validation result for all form elements or for elements with id prefix
     * @param idprefix
     */
    _clearValidation : function(idprefix)
    {
        var prefix = '';
        if (idprefix) {
            prefix = '[id*="' + idprefix + ':"]';
            $$(prefix).each(function(el){
                el.up().removeClassName('validation-failed')
                    .removeClassName('validation-passed')
                    .removeClassName('validation-error');
            });
        } else {
            this.formValidator.reset();
        }
        $$('.validation-advice' + prefix).invoke('remove');
        $$('.validation-failed' + prefix).invoke('removeClassName', 'validation-failed');
        $$('.validation-passed' + prefix).invoke('removeClassName', 'validation-passed');
        $$('.validation-error' + prefix).invoke('removeClassName', 'validation-error');
    },

    /**
     * Attempt to submit order
     */
    _submitOrder : function()
    {
        if (this._canSubmitOrder && (this.reloadByShippingSelect || this._validateForm())) {
            this.form.submit();
            this._updateOrderSubmit(true);
            if (this._ubpdateOrderButton) {
                this._ubpdateOrderButton.addClassName('no-checkout');
                this._ubpdateOrderButton.setStyle({opacity:.5});
            }
            if (this._pleaseWait) {
                this._pleaseWait.show();
            }
            return;
        }
        this._updateOrderSubmit(true);
    },

    /**
     * Explicitly enable order submission
     */
    _onSubmitShippingSuccess : function()
    {
        this._updateOrderSubmit(false);
        if (this.onSubmitShippingSuccess) {
            this.onSubmitShippingSuccess();
        }
    },

    /**
     * Check/Set whether order can be submitted
     * Also disables form submission element, if any
     * @param shouldDisable - whether should prevent order submission explicitly
     */
    _updateOrderSubmit : function(shouldDisable)
    {
        var isDisabled = shouldDisable || (
            this.reloadByShippingSelect && (!this.shippingSelect || '' == this.shippingSelect.value)
        );
        this._canSubmitOrder = !isDisabled;
        if (this.formSubmit) {
            this._toggleButton(this.formSubmit, isDisabled);
        }
    },

    /**
     * Enable/Disable button
     *
     * @param button
     * @param disable
     */
    _toggleButton : function(button, disable)
    {
        button.disabled = disable;
        button.removeClassName('no-checkout');
        button.setStyle({opacity:1});
        if (disable) {
            button.addClassName('no-checkout');
            button.setStyle({opacity:.5});
        }
    }
};
