/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'uiComponent',
        'jquery',
        'domReady!'
    ],
    function (
        Component,
        $
    ) {
        'use strict';

        return Component.extend({

            defaults: {},

            /**
             * @returns {Object}
             */
            initialize: function () {
                this._super();

                return this.initEvents();
            },

            /**
             * @returns {Object}
             */
            initEvents: function () {
                $('#' + this.id).off('click')
                    .on('click', this.click.bind(this));

                return this;
            },

            /**
             * @returns void
             */
            click: function (event) {
                var $body = $('body'),
                    $this = $(event.currentTarget),
                    data = {
                        amount: $this.data('amount'),
                        locale: $this.data('locale'),
                        currency: $this.data('currency')
                    };

                event.preventDefault();

                $body.trigger('processStart');
                $body.trigger('braintreePaypalClick', [data]);
            }
        });
    }
);
