/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "prototype"
], function(jQuery){

if (typeof Mage == 'undefined') {
    window.Mage = {};
}
if (typeof Mage.GoogleShopping == 'undefined') {
    Mage.GoogleShopping = {
        productForm: null,
        productGrid: null,

        poller: {
            timeout: 10000,
            interval: null,

            start: function(url) {
                this.interval = setInterval(this.request.bind(this, url), this.timeout)
            },

            stop: function() {
                clearInterval(this.interval);
            },

            request: function(url) {
                new Ajax.Request(url, {
                    method: 'get',
                    onComplete: (function (response) {
                        this.onSuccess(response.responseJSON.is_running);
                    }).bind(this)
                })
            },

            onSuccess: function(isFinished) {

            }
        },

        startAction: function (form) {
            jQuery.ajax({
                url: form.action,
                'type': 'post',
                'data': form.serialize(true),
                'success': Mage.GoogleShopping.onSuccess.bind(Mage.GoogleShopping, this),
                'error': Mage.GoogleShopping.onFailure.bind(Mage.GoogleShopping, this),
                'showLoader': true,
                'loaderContext': form
            });
        },

        onSuccess: function(form, response) {
            if (response.responseJSON && typeof response.responseJSON.redirect != 'undefined') {
                setLocation(response.responseJSON.redirect);
            } else {
                window.location.reload();
            }
        },

        onFailure: function() {
            window.location.reload();
        },

        lock: function() {
            if (this.itemForm) {
                this.lockButton($(this.itemForm).down('button'));
            }
            if (this.productForm) {
                this.lockButton($(this.productForm).down('button'));
            }
            this.addMessage();
        },

        addMessage: function() {
            var messageBox = $('messages');
            var messageList = $(messageBox).down('.messages');
            if (!messageList) {
                messageList = new Element('div', {class: 'messages'});
                messageBox.update(messageList);
            }
            var message = '<div class="message notice"><div>' + this.runningMessage + '</div></div>';
            messageList.update(message);
        },

        lockButton: function (button) {
            $(button).addClassName('disabled');
            $(button).disabled = true;
        }
    }
}


    jQuery(function(){
        
        setTimeout(function(){
            Mage.GoogleShopping.itemForm = items_massactionJsObject.form;
            
            items_massactionJsObject.prepareForm = items_massactionJsObject.prepareForm.wrap(function (proceed) {
                Mage.GoogleShopping.itemForm = proceed();
                Mage.GoogleShopping.itemForm.submit = function(){ Mage.GoogleShopping.startAction(this); };
                return Mage.GoogleShopping.itemForm;
            });

            Mage.GoogleShopping.productForm = googleshopping_selection_search_grid__massactionJsObject.form;
           
            googleshopping_selection_search_grid__massactionJsObject.prepareForm = googleshopping_selection_search_grid__massactionJsObject.prepareForm.wrap(function (proceed) {
                Mage.GoogleShopping.productForm = proceed();
                Mage.GoogleShopping.productForm.submit = function() { Mage.GoogleShopping.startAction(this) };
                return Mage.GoogleShopping.productForm;
            });

            Mage.GoogleShopping.itemForm.submit = function(){ Mage.GoogleShopping.startAction(this); };
            Mage.GoogleShopping.productForm.submit = function() { Mage.GoogleShopping.startAction(this) };
            
            if (Mage.GoogleShopping.isProcessRunning) {
                Mage.GoogleShopping.lock();
                Mage.GoogleShopping.poller.onSuccess = function(isRunning){
                    if (!isRunning) {
                        this.stop()
                        Mage.GoogleShopping.onSuccess();
                    }
                }
                Mage.GoogleShopping.poller.start(Mage.GoogleShopping.statusUrl);
            }

        }, 1500);
    });

});