/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
define([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'jquery/ui',
    'mage/mage'
], function ($, confirm) {
    'use strict';

    $.widget('mage.paypalCheckout', {
        /**
         * Initialize store credit events
         * @private
         */
        _create: function () {
            this.element.on('click', '[data-action="checkout-form-submit"]', $.proxy(function (e) {
                var returnUrl = $(e.target).data('checkout-url'),
                    self = this;

                e.preventDefault();

                if (this.options.confirmUrl && this.options.confirmMessage) {
                    confirm({
                        content: this.options.confirmMessage,
                        actions: {
                            confirm: function() {
                                returnUrl = self.options.confirmUrl;
                                self._redirect(returnUrl);
                            },
                            cancel: function() {
                                self.redirect(returnUrl);
                            }
                        }
                    });

                    return false;
                }

                this._redirect(returnUrl);

            }, this));
        },
        _redirect: function(returnUrl) {
            var form;

            if (this.options.isCatalogProduct) {
                // find the form from which the button was clicked
                form = $(this.options.shortcutContainerClass).closest('form');

                $(form).find(this.options.paypalCheckoutSelector).val(returnUrl);
                $(form).submit();
            } else {
                $.mage.redirect(returnUrl);
            }
        }
    });

    return $.mage.paypalCheckout;
});
