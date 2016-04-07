/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    "mage/template",
    "jquery/ui"
], function($,mageTemplate){
    
    $.widget('mage.dataPost', {
        options: {
            formTemplate: '<form action="<%- data.action %>" method="post">'
                + '<% _.each(data.data, function(value, index) { %>'
                    + '<input name="<%- index %>" value="<%- value %>">'
                + '<% }) %></form>',
            postTrigger: ['a[data-post]', 'button[data-post]', 'span[data-post]'],
            formKeyInputSelector: 'input[name="form_key"]'
        },
        _create: function() {
            this._bind();
        },
        _bind: function() {
            var events = {};
            $.each(this.options.postTrigger, function(index, value) {
                events['click ' + value] = '_postDataAction';
            });
            this._on(events);
        },
        _postDataAction: function(e) {
            e.preventDefault();
            var params = $(e.currentTarget).data('post');
            this.postData(params);
        },
        postData: function(params) {
            var formKey = $(this.options.formKeyInputSelector).val();
            if (formKey) {
                params.data.form_key = formKey;
            }
            $(mageTemplate(this.options.formTemplate, {
                data: params
            })).appendTo('body').hide().submit();
        }
    });
    
    $(document).dataPost();

    return $.mage.dataPost;
});