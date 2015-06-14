/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, _) {
    'use strict';

    $.widget('mage.confirm', $.mage.modal, {
        options: {
            modalClass: 'confirm',
            title: '',
            actions: {
                always: function(){},
                confirm: function(){},
                cancel: function(){}
            },
            buttons: [{
                text: $.mage.__('Cancel'),
                class: 'action-tertiary',
                click: function(){
                    this.closeModal();
                }
            }, {
                text: $.mage.__('OK'),
                class: 'action-secondary',
                click: function() {
                    this.closeModal(true);
                }
            }]
        },
        _create: function() {
            this._super();
            this.modal.find(this.options.modalCloseBtn).off().on('click',  _.bind(this.closeModal, this, false));
            this.openModal();
        },
        _remove: function() {
            this.modal.remove();
        },
        openModal: function() {
            return this._super();
        },
        closeModal: function(result) {
            result = result || false;

            if (result) {
                this.options.actions.confirm();
            } else {
                this.options.actions.cancel();
            }
            this.options.actions.always();
            this.element.bind('confirmclosed', _.bind(this._remove, this));

            return this._super();
        }
    });

    return function (config) {
        return $('<div></div>').html(config.content).confirm(config);
    };
});
