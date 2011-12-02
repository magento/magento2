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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
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
    onSubmitShippingSuccess : false,

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

        if (shippingSubmitForm && shippingSelect) {
            this.shippingSelect = shippingSelect;
            Event.observe(shippingSelect, 'change', this._submitShipping.bindAsEventListener(this, shippingSubmitForm.action, shippingResultId));
            this._updateOrderSubmit(false);
        } else {
            this._canSubmitOrder = true;
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
     * Attempt to submit order
     */
    _submitOrder : function()
    {
        if (this._canSubmitOrder) {
            this.form.submit();
            this._updateOrderSubmit(true);
            if (this._pleaseWait) {
                this._pleaseWait.show();
            }
        }
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
        var isDisabled = shouldDisable || !this.shippingSelect || '' == this.shippingSelect.value;
        this._canSubmitOrder = !isDisabled;
        if (this.formSubmit) {
            this.formSubmit.disabled = isDisabled;
            this.formSubmit.removeClassName('no-checkout');
            this.formSubmit.setStyle({opacity:1});
            if (isDisabled) {
                this.formSubmit.addClassName('no-checkout');
                this.formSubmit.setStyle({opacity:.5});
            }
        }
    }
};
