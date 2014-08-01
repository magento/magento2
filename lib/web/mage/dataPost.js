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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
});