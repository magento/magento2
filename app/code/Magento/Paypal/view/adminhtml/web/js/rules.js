/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'uiClass',
    'Magento_Ui/js/modal/alert'
], function (_, Class, alert) {
    'use strict';

    return Class.extend({
        defaults: {

            /**
             * Payment conflicts checker
             */
            executed: false
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        simpleDisable: function ($target, $owner, data) {
            $target.find(data.enableButton + ' option[value="0"]').prop('selected', true);
            $target.find('label.enabled').removeClass('enabled');
            $target.find('.section-config').removeClass('enabled');
        },

        /**
         * @param {*} $target
         */
        simpleMarkEnable: function ($target) {
            $target.find('.section-config').addClass('enabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        disable: function ($target, $owner, data) {
            this.simpleDisable($target, $owner, data);
            $target.find(data.enableButton).change();
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        conflict: function ($target, $owner, data) {
            var isDisabled = true,
                newLine = String.fromCharCode(10, 13);

            if ($owner.find(data.enableButton).val() === '1') {
                _.every(data.argument, function (name) {
                    if (data.solutionsElements[name] &&
                        data.solutionsElements[name].find(data.enableButton).val() === '1'
                    ) {
                        isDisabled = false;

                        return isDisabled;
                    }

                    return isDisabled;
                }, this);

                if (!isDisabled && !this.executed) {
                    this.executed = true;
                    alert({
                        content: 'The following error(s) occurred:' +
                        newLine +
                        'Some PayPal solutions conflict.' +
                        newLine +
                        'Please re-enable the previously enabled payment solutions.'
                    });
                }
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalExpressDisable: function ($target, $owner, data) {
            $target.find(data.enableButton + ' option[value="0"]').prop('selected', true);
            $target.find('label.enabled').removeClass('enabled');
            $target.find(data.enableButton).change();
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalExpressLockConfiguration: function ($target, $owner, data) {
            $target.find(data.buttonConfiguration).addClass('disabled')
                .attr('disabled', 'disabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalExpressLockConfigurationConditional: function ($target, $owner, data) {
            var isDisabled = true;

            _.every(data.argument, function (name) {
                if (data.solutionsElements[name] &&
                    data.solutionsElements[name].find(data.enableButton).val() === '1'
                ) {
                    isDisabled = false;

                    return isDisabled;
                }

                return isDisabled;
            }, this);

            if (!isDisabled &&
                $target.find(data.enableInContextPayPal).val() === '0') {
                this.paypalExpressLockConfiguration($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalExpressMarkDisable: function ($target, $owner, data) {
            var isDisabled = true;

            _.every(data.argument, function (name) {
                if (data.solutionsElements[name] &&
                    data.solutionsElements[name].find(data.enableButton).val() === '1'
                ) {
                    isDisabled = false;

                    return isDisabled;
                }

                return isDisabled;
            }, this);

            if (isDisabled) {
                this.simpleDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalExpressUnlockConfiguration: function ($target, $owner, data) {
            var isUnlock = true;

            _.every(data.argument, function (name) {
                if (data.solutionsElements[name] &&
                    data.solutionsElements[name].find(data.enableButton).val() === '1'
                ) {
                    isUnlock = false;

                    return isUnlock;
                }

                return isUnlock;
            }, this);

            if (isUnlock) {
                $target.find(data.buttonConfiguration).removeClass('disabled')
                    .removeAttr('disabled');
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalBmlDisable: function ($target, $owner, data) {
            $target.find('label[for="' + $target.find(data.enableBmlPayPal).attr('id') + '"]').removeClass('enabled');
            $target.find(data.enableBmlPayPal + ' option[value="0"]').prop('selected', true);
            $target.find(data.enableBmlPayPal).prop('disabled', true);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalBmlDisableConditional: function ($target, $owner, data) {
            if ($target.find(data.enableButton).val() === '0') {
                this.paypalBmlDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        paypalBmlEnable: function ($target, $owner, data) {
            $target.find(data.enableBmlPayPal).prop('disabled', false);
            $target.find(data.enableBmlPayPal + ' option[value="1"]').prop('selected', true);
            $target.find('label[for="' + $target.find(data.enableBmlPayPal).attr('id') + '"]').addClass('enabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressDisable: function ($target, $owner, data) {
            $target.find('label[for="' + $target.find(data.enableExpress).attr('id') + '"]').removeClass('enabled');
            $target.find(data.enableExpress + ' option[value="0"]').prop('selected', true);
            $target.find(data.enableExpress).prop('disabled', true);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressDisableConditional: function ($target, $owner, data) {
            if (data.argument) {
                var isDisabled = true;

                _.every(data.argument, function (name) {
                    if (data.solutionsElements[name] &&
                        data.solutionsElements[name].find(data.enableButton).val() === '1'
                    ) {
                        isDisabled = false;

                        return isDisabled;
                    }

                    return isDisabled;
                }, this);

                if (isDisabled) {
                    this.payflowExpressDisable($target, $owner, data);
                }
            } else {
                if ($target.find(data.enableButton).val() === '0') {
                    this.payflowExpressDisable($target, $owner, data);
                    $target.find(data.enableExpress).change();
                }
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressEnable: function ($target, $owner, data) {
            $target.find(data.enableExpress).prop('disabled', false);
            $target.find(data.enableExpress + ' option[value="1"]').prop('selected', true);
            $target.find('label[for="' + $target.find(data.enableExpress).attr('id') + '"]').addClass('enabled');
            $target.find(data.enableExpress).change();
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressEnableConditional: function ($target, $owner, data) {
            var isDisabled = true;

            _.every(data.argument, function (name) {
                if (data.solutionsElements[name] &&
                    data.solutionsElements[name].find(data.enableButton).val() === '1'
                ) {
                    isDisabled = false;

                    return isDisabled;
                }

                return isDisabled;
            }, this);

            if (!isDisabled) {
                $target.find(data.enableExpress).prop('disabled', true);
                $target.find(data.enableExpress + ' option[value="1"]').prop('selected', true);
                $target.find('label[for="' + $target.find(data.enableExpress).attr('id') + '"]').addClass('enabled');
            } else {
                $target.find('label[for="' + $target.find(data.enableExpress).attr('id') + '"]').removeClass('enabled');
                $target.find(data.enableExpress + ' option[value="0"]').prop('selected', true);
                $target.find(data.enableExpress).prop('disabled', true);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressLockConditional: function ($target, $owner, data) {
            if ($target.find(data.enableButton).val() === '0') {
                $target.find(data.enableExpress).prop('disabled', true);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressUsedefaultDisable: function ($target, $owner, data) {
            $target.find('input[id="' + $target.find(data.enableExpress).attr('id') + '_inherit"]')
                .prop('checked', false);
            this.payflowExpressEnable($target, $owner, data);
            $target.find(data.enableExpress).change();
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowExpressUsedefaultEnable: function ($target, $owner, data) {
            $target.find('input[id="' + $target.find(data.enableExpress).attr('id') + '_inherit"]')
                .prop('checked', true);
            this.payflowExpressDisable($target, $owner, data);
            $target.find(data.enableExpress).change();
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowBmlDisable: function ($target, $owner, data) {
            $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').removeClass('enabled');
            $target.find(data.enableBml + ' option[value="0"]').prop('selected', true);
            $target.find(data.enableBml).prop('disabled', true);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowBmlDisableConditional: function ($target, $owner, data) {
            if (data.argument) {
                var isDisabled = true;

                _.every(data.argument, function (name) {
                    if (data.solutionsElements[name] &&
                        data.solutionsElements[name].find(data.enableButton).val() === '1'
                    ) {
                        isDisabled = false;

                        return isDisabled;
                    }

                    return isDisabled;
                }, this);

                if (isDisabled) {
                    this.payflowBmlDisable($target, $owner, data);
                }
            } else {
                if ($target.find(data.enableButton).val() === '0') {
                    this.payflowBmlDisable($target, $owner, data);
                }
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowBmlDisableConditionalExpress: function ($target, $owner, data) {
            if ($target.find(data.enableExpress).val() === '0') {
                this.payflowBmlDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowBmlEnable: function ($target, $owner, data) {
            $target.find(data.enableBml).prop('disabled', false);
            $target.find(data.enableBml + ' option[value="1"]').prop('selected', true);
            $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').addClass('enabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowBmlEnableConditional: function ($target, $owner, data) {
            var isDisabled = true;

            _.every(data.argument, function (name) {
                if (data.solutionsElements[name] &&
                    data.solutionsElements[name].find(data.enableButton).val() === '1'
                ) {
                    isDisabled = false;

                    return isDisabled;
                }

                return isDisabled;
            }, this);

            if (!isDisabled) {
                $target.find(data.enableBml).prop('disabled', true);
                $target.find(data.enableBml + ' option[value="1"]').prop('selected', true);
                $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').addClass('enabled');
            } else {
                $target.find('label[for="' + $target.find(data.enableBml).attr('id') + '"]').removeClass('enabled');
                $target.find(data.enableBml + ' option[value="0"]').prop('selected', true);
                $target.find(data.enableBml).prop('disabled', true);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        payflowBmlLockConditional: function ($target, $owner, data) {
            if ($target.find(data.enableButton).val() === '0') {
                $target.find(data.enableBml).prop('disabled', true);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        inContextEnable: function ($target, $owner, data) {
            $target.find(data.enableInContextPayPal).prop('disabled', false);
            $target.find(data.enableInContextPayPal + ' option[value="1"]').prop('selected', true);
            $target.find('label[for="' + $target.find(data.enableInContextPayPal).attr('id') + '"]')
                .addClass('enabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        inContextDisable: function ($target, $owner, data) {
            $target.find('label[for="' + $target.find(data.enableInContextPayPal).attr('id') + '"]')
                .removeClass('enabled');
            $target.find(data.enableInContextPayPal + ' option[value="0"]').prop('selected', true);
            $target.find(data.enableInContextPayPal).prop('disabled', true);
        },

        /**
         * @param {*} $target
         */
        inContextShowMerchantId: function ($target) {
            $target.find('tr[id$="_merchant_id"], input[id$="_merchant_id"]').show();
            $target.find('input[id$="_merchant_id"]').attr('disabled', false);
        },

        /**
         * @param {*} $target
         */
        inContextHideMerchantId: function ($target) {
            $target.find('tr[id$="_merchant_id"], input[id$="_merchant_id"]').hide();
            $target.find('input[id$="_merchant_id"]').attr('disabled', true);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        inContextActivate: function ($target, $owner, data) {
            $target.find('label[for="' + $target.find(data.enableInContextPayPal).attr('id') + '"]')
                .addClass('enabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        inContextDeactivate: function ($target, $owner, data) {
            $target.find('label[for="' + $target.find(data.enableInContextPayPal).attr('id') + '"]')
                .removeClass('enabled');
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {String} data
         */
        inContextDisableConditional: function ($target, $owner, data) {
            if ($target.find(data.enableButton).val() === '0') {
                this.inContextDisable($target, $owner, data);
            }
        }
    });
});
