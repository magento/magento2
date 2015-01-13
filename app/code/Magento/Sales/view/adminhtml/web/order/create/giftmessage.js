/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/********************* GIFT OPTIONS POPUP ***********************/
/********************* GIFT OPTIONS SET ***********************/
define([
    "jquery",
    "jquery/ui",
    "mage/translate",
    "mage/validation",
    "prototype"
], function(jQuery){

window.giftMessagesController = {
    toogleRequired: function(source, objects)
    {
        if(!$(source).value.blank()) {
            objects.each(function(item) {
               $(item).addClassName('required-entry');
               var label = findFieldLabel($(item));
               if (label) {
                   var span = label.down('span');
                   if (!span) {
                       Element.insert(label, {bottom: '&nbsp;<span class="required">*</span>'});
                   }
               }
            });
        } else {
            objects.each(function(item) {
                if($(source).formObj && $(source).formObj.validator) {
                    $(source).formObj.validator.reset(item);
                }
                $(item).removeClassName('required-entry');
                var label = findFieldLabel($(item));
                if (label) {
                    var span = label.down('span');
                    if (span) {
                        Element.remove(span);
                    }
                }
                // Hide validation advices if exist
                if ($(item) && $(item).advices) {
                    $(item).advices.each(function (pair) {
                        if (pair.value != null) pair.value.hide();
                    });
                }
            });
        }
    },
    toogleGiftMessage: function(container) {
        if(!$(container).toogleGiftMessage) {
            $(container).toogleGiftMessage = true;
            $(this.getFieldId(container, 'edit')).show();
            $(container).down('.action-link').addClassName('open');
            $(container).down('.default-text').hide();
            $(container).down('.close-text').show();
        } else {
            $(container).toogleGiftMessage = false;
            $(this.getFieldId(container, 'message')).formObj = $(this.getFieldId(container, 'form'));
            var form = jQuery('#' + this.getFieldId(container, 'form'));
            jQuery('#' + this.getFieldId(container, 'form')).validate({errorClass: 'mage-error'});

            if(!form.valid()) {
                return false;
            }

            new Ajax.Request($(this.getFieldId(container, 'form')).action, {
                parameters: Form.serialize($(this.getFieldId(container, 'form')), true),
                loaderArea: container,
                onComplete: function(transport) {

                    $(container).down('.action-link').removeClassName('open');
                    $(container).down('.default-text').show();
                    $(container).down('.close-text').hide();
                    $(this.getFieldId(container, 'edit')).hide();
                    if (transport.responseText.match(/YES/g)) {
                        $(container).down('.default-text').down('.edit').show();
                        $(container).down('.default-text').down('.add').hide();
                    } else {
                        $(container).down('.default-text').down('.add').show();
                        $(container).down('.default-text').down('.edit').hide();
                    }

                }.bind(this)
            });
        }

        return false;
    },
    saveGiftMessage: function(container) {
        $(this.getFieldId(container, 'message')).formObj = $(this.getFieldId(container, 'form'));

        var form = jQuery('#' + this.getFieldId(container, 'form'));
        form.validate({errorClass: 'mage-error'});

        if(!form.valid()) {
            return;
        }

        new Ajax.Request($(this.getFieldId(container, 'form')).action, {
            parameters: Form.serialize($(this.getFieldId(container, 'form')), true),
            loaderArea: container
        });
    },
    getFieldId: function(container, name) {
        return container + '_' + name;
    }
};

function findFieldLabel(field) {
    var tdField = $(field).up('td');
    if (tdField) {
       var tdLabel = tdField.previous('td');
       if (tdLabel) {
           var label = tdLabel.down('label');
           if (label) {
               return label;
           }
       }
    }

    return false;
}

window.findFieldLabel = findFieldLabel;

window.GiftOptionsPopup = Class.create();
GiftOptionsPopup.prototype = {
    //giftOptionsWindowMask: null,
    giftOptionsWindow: null,

    initialize: function() {
        $$('.action-link').each(function (el) {
            Event.observe(el, 'click', this.showItemGiftOptions.bind(this));
        }, this);

        // Move gift options popup to start of body, because soon it will contain FORM tag that can break DOM layout if within other FORM
        var oldPopupContainer = $('gift_options_configure');
        if (oldPopupContainer) {
            oldPopupContainer.remove();
        }

        var newPopupContainer = $('gift_options_configure_new');
        $(document.body).insert({top: newPopupContainer});
        newPopupContainer.id = 'gift_options_configure';

        // Put controls container inside a FORM tag so we can use Validator
        var form = new Element('form', {action: '#', id: 'gift_options_configuration_form', method: 'post'});
        var formContents = $('gift_options_form_contents');
        if (formContents) {
            formContents.parentNode.appendChild(form);
            form.appendChild(formContents);
        }

        this.giftOptionsWindow = $('gift_options_configure');

        jQuery(this.giftOptionsWindow).dialog({
            autoOpen:   false,
            modal:      true,
            resizable:  false,
            minWidth:   500,
            dialogClass: 'gift-options-popup'
        });
    },

    showItemGiftOptions : function(event) {
        var element = Event.element(event).id;
        var itemId = element.sub('gift_options_link_','');

        jQuery(this.giftOptionsWindow).dialog('open');

        this.setTitle(itemId);

        Event.observe($('gift_options_cancel_button'), 'click', this.onCloseButton.bind(this));
        Event.observe($('gift_options_ok_button'), 'click', this.onOkButton.bind(this));
        Event.stop(event);
    },

    setTitle : function (itemId) {
        var productTitleElement = $('order_item_' + itemId + '_title');
        var productTitle = '';
        if (productTitleElement) {
            productTitle = productTitleElement.innerHTML;
        }
        jQuery(this.giftOptionsWindow).dialog({ title: jQuery.mage.__('Gift Options for ') + productTitle });
    },

    onOkButton : function() {
        var giftOptionsForm = jQuery('#gift_options_configuration_form');
        if (!giftOptionsForm.validate({errorClass: 'mage-error'}).valid()) {
            return false;
        }
        if (jQuery.isFunction(giftOptionsForm[0].reset)) {
            giftOptionsForm[0].reset();
        }
        this.closeWindow();
        return true;
    },

    onCloseButton : function() {
        this.closeWindow();
    },

    closeWindow : function() {
        jQuery(this.giftOptionsWindow).dialog('close');
    }
}


window.GiftMessageSet = Class.create();

GiftMessageSet.prototype = {
    destPrefix: 'current_item_giftmessage_',
    sourcePrefix: 'giftmessage_',
    fields: ['sender', 'recipient', 'message'],
    isObserved: false,
    callback: null,

    initialize: function() {
        $$('.action-link').each(function (el) {
            Event.observe(el, 'click', this.setData.bind(this));
        }, this);
    },

    setData: function(event) {
        var element = Event.element(event).id;
        this.id = element.sub('gift_options_link_','');

        if ($('gift-message-form-data-' + this.id)) {
            this.fields.each(function(el) {
                if ($(this.sourcePrefix + this.id + '_' + el) && $(this.destPrefix + el)) {
                    $(this.destPrefix + el).value = $(this.sourcePrefix + this.id + '_' + el).value
                }
            }, this);
            $('gift_options_giftmessage').show();
        } else if ($('gift_options_giftmessage')) {
            $('gift_options_giftmessage').hide();
        }

        if (!this.isObserved) {
            Event.observe('gift_options_ok_button', 'click', this.saveData.bind(this));
            this.isObserved = true;
        }
    },

    prepareSaveData: function() {
        var hash = $H();
        $$("div[id^=gift_options_data_]").each(function (el) {
            var fields = el.select('input', 'select', 'textarea');
            var data = Form.serializeElements(fields, true);
            hash.update(data);
        });
        return hash;
    },

    setSaveCallback: function(callback) {
        if (typeof callback == 'function') {
            this.callback = callback;
        }
    },

    saveData: function(event){
        this.fields.each(function(el) {
            if ($(this.sourcePrefix + this.id + '_' + el) && $(this.destPrefix + el)) {
                $(this.sourcePrefix + this.id + '_' + el).value = $(this.destPrefix + el).value;
            }
        }, this);
        if ($(this.sourcePrefix + this.id + '_form')) {
            $(this.sourcePrefix + this.id + '_form').request();
        } else if (typeof(order) != 'undefined') {
            var data = this.prepareSaveData();
            var self = this;
            jQuery.when(order.loadArea(['items'], true, data.toObject())).done(function() {
                if (self.callback !== null) {
                    self.callback();
                }
            });
        }
    }
};

});