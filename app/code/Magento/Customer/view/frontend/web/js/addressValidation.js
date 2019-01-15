/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'mageUtils',
    'mage/translate',
    'jquery/ui',
    'validation'
], function ($, __, utils, $t) {
    'use strict';

    $.widget('mage.addressValidation', {
        options: {
            selectors: {
                button: '[data-action=save-address]',
                zip: '#zip',
                country: 'select[name="country_id"]:visible'
            }
        },

        validatedPostCodeExample: [],
        zipInput: null,
        countrySelect: null,

        /**
         * Validation creation
         * @protected
         */
        _create: function () {
            var button = $(this.options.selectors.button, this.element);

            this.zipInput = $(this.options.selectors.zip, this.element);
            this.countrySelect = $(this.options.selectors.country, this.element);

            this.element.validation({

                /**
                 * Submit Handler
                 * @param {Element} form - address form
                 */
                submitHandler: function (form) {

                    button.attr('disabled', true);
                    form.submit();
                }
            });

            this._addPostCodeValidation();
        },

        /**
         * Add postcode validation
         */
        _addPostCodeValidation: function () {
            var self = this;

            this.zipInput.on('keyup', __.debounce(function (event) {
                    var valid = self._validatePostCode(event.target.value);

                    self._renderValidationResult(valid);
                }, 500)
            );

            this.countrySelect.on('change', function () {
                var valid = self._validatePostCode(self.zipInput.val());

                self._renderValidationResult(valid);
            });
        },

        /**
         * Validate post code value.
         *
         * @param {String} postCode - post code
         * @return {Boolean} Whether is post code valid
         */
        _validatePostCode: function (postCode) {
            var countryId = this.countrySelect.val(),
                patterns = this.options.postCodes[countryId],
                pattern, regex;

            if (postCode === null) {
                return true;
            }

            this.validatedPostCodeExample = [];

            if (!utils.isEmpty(postCode) && !utils.isEmpty(patterns)) {
                for (pattern in patterns) {
                    if (patterns.hasOwnProperty(pattern)) { //eslint-disable-line max-depth
                        this.validatedPostCodeExample.push(patterns[pattern].example);
                        regex = new RegExp(patterns[pattern].pattern);

                        if (regex.test(postCode)) { //eslint-disable-line max-depth
                            return true;
                        }
                    }
                }

                return false;
            }

            return true;
        },

        /**
         * Renders warning messages for invalid post code.
         *
         * @param {Boolean} valid
         */
        _renderValidationResult: function (valid) {
            var warnMessage,
                alertDiv = this.zipInput.next();

            if (!valid) {
                warnMessage = $t('Provided Zip/Postal Code seems to be invalid.');

                if (this.validatedPostCodeExample.length) {
                    warnMessage += $t(' Example: ') + this.validatedPostCodeExample.join('; ') + '. ';
                }
                warnMessage += $t('If you believe it is the right one you can ignore this notice.');
            }

            alertDiv.children(':first').text(warnMessage);

            if (valid) {
                alertDiv.hide();
            } else {
                alertDiv.show();
            }
        }
    });

    return $.mage.addressValidation;
});
