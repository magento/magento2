/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/*jshint browser:true jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    "jquery/ui",
    "jquery/template"
], function($){
    
    $.widget('mage.dataPost', {
        options: {
            formTemplate: '<form action="${action}" method="post">{{each data}}<input name="${$index}" value="${$value}">{{/each}}</form>',
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
            $.tmpl(this.options.formTemplate, params).appendTo('body').hide().submit();
        }
    });
    
    $(document).dataPost();

    return $.mage.dataPost;
});