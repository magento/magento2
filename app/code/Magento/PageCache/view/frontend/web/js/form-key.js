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
define([
    "jquery",
    "jquery/ui",
    "mage/cookies"
], function($){

    /**
     * FormKey Widget - this widget is generating from key, saves it to cookie and
     */
    $.widget('mage.formKey', {

        options: {
            inputSelector: 'input[name="form_key"]',
            allowedCharacters: '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            length: 16
        },

        _create: function() {
            var formKey = $.mage.cookies.get('form_key');
            if (!formKey) {
                formKey = this._generate(this.options.allowedCharacters, this.options.length);
                var date = new Date();
                date.setTime(date.getTime() + (365 * 24 * 60 * 60 * 1000));
                $.mage.cookies.set('form_key', formKey, {expires: date, path: '/'});
            }
            $(this.options.inputSelector).val(formKey);
        },

        _generate: function(chars, length) {
            var result = '';
            for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
            return result;
        }
    });

    $(function() {
        $('body').formKey();
    });

});