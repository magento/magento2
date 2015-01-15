/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(["prototype"], function(){

window.centinelValidator = new Class.create();

centinelValidator.prototype = {

    initialize : function(method, validationUrl, containerId){
        this.method = method;
        this.validationUrl = validationUrl;
        this.containerId = containerId;
    },

    validate : function(){
        if (order.paymentMethod != this.method) {
            return false;
        }
        var params = order.getPaymentData();
        params = order.prepareParams(params);
        params.json = true;

        new Ajax.Request(this.validationUrl, {
            parameters:params,
            method:'post',
            onSuccess: function(transport) {
            var response = transport.responseText.evalJSON();
                if (response.authenticationUrl) {
                    this.autenticationStart(response.authenticationUrl);
                }
                if (response.message) {
                    this.autenticationFinish(response.message);
                }
            }.bind(this)
        });
    },

    autenticationStart : function(url) {
        this.getContainer().src = url;
        this.getContainer().style.display = 'block';
    },

    autenticationFinish : function(message) {
        alert(message);
        this.getContainer().style.display = 'none';
    },

    getContainer : function() {
        return $(this.containerId);
    }

};

});