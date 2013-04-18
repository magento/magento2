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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
/*jshint jquery:true*/
(function ($) {
    $.widget('vde.vdeThemeInit', {
        options: {
            isPhysicalTheme: 0,
            createVirtualThemeUrl: null,
            registerElementsEvent : 'registerElements'
        },

        /**
         * Initialize widget
         *
         * @protected
         */
        _create: function() {
            if (this.options.isPhysicalTheme) {
                this._bind();
            }
        },

        /**
         * Bind event handlers
         *
         * @protected
         */
        _bind: function() {
            var body = $('body');
            body.on(this.options.registerElementsEvent, $.proxy(this._onRegisterElements, this));
        },

        /**
         * Event handler
         *
         * @param e
         * @param data
         * @protected
         */
        _onRegisterElements: function(e, data){
            var content = data.content || 'body';
            content = $(content).contents();
            this._registerElements(content, data.elements);
        },

        /**
         * Register elements
         *
         * @param content
         * @param elements
         * @protected
         */
        _registerElements: function(content, elements) {
            for (var eventType in elements) {
                for (var i = 0; i < elements[eventType].length; i++){
                    content.find(elements[eventType][i]).on(eventType, $.proxy(this._onChangeTheme, this));
                }
            }
        },

        /**
         * Manage change theme event
         *
         * @param event
         * @protected
         */
        _onChangeTheme: function(event) {
            if (confirm($.mage.__('You want to change theme. It is necessary to create customization. Do you want to create?'))) {
                this._createVirtualTheme();
            }
            event.stopPropagation();
            $(event.target).blur();
            return false;
        },

        /**
         * Create virtual theme
         *
         * @protected
         */
        _createVirtualTheme: function() {
            $.ajax({
                url: this.options.createVirtualThemeUrl,
                type: "GET",
                dataType: 'JSON',
                success: $.proxy(function (data) {
                    if (!data.error) {
                        this._launchVirtualTheme(data.redirect_url);
                    } else {
                        alert(data.message);
                    }
                }, this),

                error: function(data) {
                    throw Error($.mage.__('Some problem with save action'));
                }
            });
        },

        /**
         * Launch virtual theme
         *
         * @param {String} redirectUrl
         * @protected
         */
        _launchVirtualTheme: function(redirectUrl) {
            window.location.replace(redirectUrl);
        }
    });
})(jQuery);
