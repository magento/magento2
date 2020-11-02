/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/ui-select',
    'jquery',
    'underscore'
], function (Select, $, _) {
    'use strict';

    return Select.extend({
        defaults: {
            bookmarkProvider: 'ns = ${ $.ns }, index = bookmarks',
            filterChipsProvider: 'componentType = filters, ns = ${ $.ns }',
            validationUrl: false,
            loadedOption: [],
            validationLoading: true,
            imports: {
                applied: '${ $.filterChipsProvider }:applied',
                activeIndex: '${ $.bookmarkProvider }:activeIndex'
            },
            modules: {
                filterChips: '${ $.filterChipsProvider }'
            },
            listens: {
                activeIndex: 'validateInitialValue',
                applied: 'validateInitialValue'
            }

        },

        /**
         * Initializes UiSelect component.
         *
         * @returns {UiSelect} Chainable.
         */
        initialize: function () {
            this._super();

            this.validateInitialValue();

            return this;
        },

        /**
         * Validate initial value actually exists
         */
        validateInitialValue: function () {
            if (_.isEmpty(this.value())) {
                this.validationLoading(false);

                return;
            }

            $.ajax({
                url: this.validationUrl,
                type: 'GET',
                dataType: 'json',
                context: this,
                data: {
                    ids: this.value()
                },

                /** @param {Object} response */
                success: function (response) {
                    if (!_.isEmpty(response)) {
                        this.options([]);
                        this.success({
                            options: response
                        });
                    }
                    this.filterChips().updateActive();
                },

                /** set empty array if error occurs */
                error: function () {
                    this.options([]);
                },

                /** stop loader */
                complete: function () {
                    this.validationLoading(false);
                    this.setCaption();
                }
            });
        }
    });
});
