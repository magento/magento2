/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiClass',
    'Magento_Ui/js/modal/alert'
], function (Class, alert) {
    'use strict';

    /**
     * Check is solution enabled
     *
     * @param {*} solution
     * @param {String} enabler
     * @returns {Boolean}
     */
    var isSolutionEnabled = function (solution, enabler) {
        return solution.find(enabler).val() === '1';
    },

    /**
     * Check is solution has related solutions enabled
     *
     * @param {Object} data
     * @returns {Boolean}
     */
    hasRelationsEnabled = function (data) {
        var name;

        for (name in data.argument) {
            if (
                data.solutionsElements[name] &&
                isSolutionEnabled(data.solutionsElements[name], data.enableButton)
            ) {
                return true;
            }
        }

        return false;
    },

    /**
     * Set solution select-enabler to certain option
     *
     * @param {*} solution
     * @param {String} enabler
     * @param {Boolean} enabled
     */
    setSolutionSelectEnabled = function (solution, enabler, enabled) {
        enabled = !(enabled || typeof enabled === 'undefined') ? '0' : '1';

        solution.find(enabler + ' option[value=' + enabled + ']')
            .prop('selected', true);
    },

    /**
     * Set solution property 'disabled' value
     *
     * @param {*} solution
     * @param {String} enabler
     * @param {Boolean} enabled
     */
    setSolutionPropEnabled = function (solution, enabler, enabled) {
        enabled = !(enabled || typeof enabled === 'undefined');

        solution.find(enabler).prop('disabled', enabled);
    },

    /**
     * Set/unset solution select-enabler label
     *
     * @param {*} solution
     * @param {String} enabler
     * @param {Boolean} enabled
     */
    setSolutionMarkEnabled = function (solution, enabler, enabled) {
        var solutionEnabler = solution.find('label[for="' + solution.find(enabler).attr('id') + '"]');

        enabled || typeof enabled === 'undefined' ?
            solutionEnabler.addClass('enabled') :
            solutionEnabler.removeClass('enabled');
    },

    /**
     * Set/unset solution section label
     *
     * @param {*} solution
     * @param {Boolean} enabled
     */
    setSolutionSectionMarkEnabled = function (solution, enabled) {
        var solutionSection = solution.find('.section-config');

        enabled || typeof enabled === 'undefined' ?
            solutionSection.addClass('enabled') :
            solutionSection.removeClass('enabled');
    },

    /**
     * Set/unset solution section inner labels
     *
     * @param {*} solution
     * @param {Boolean} enabled
     */
    setSolutionLabelsMarkEnabled = function (solution, enabled) {
        var solutionLabels = solution.find('label.enabled');

        enabled || typeof enabled === 'undefined' ?
            solutionLabels.addClass('enabled') :
            solutionLabels.removeClass('enabled');
    },

    /**
     * Set solution as disabled
     *
     * @param {*} solution
     * @param {String} enabler
     */
    disableSolution = function (solution, enabler) {
        setSolutionUsedefaultEnabled(solution, enabler);
        setSolutionMarkEnabled(solution, enabler, false);
        setSolutionSelectEnabled(solution, enabler, false);
        setSolutionPropEnabled(solution, enabler, false);
    },

    /**
     * Set solution as enabled
     *
     * @param {*} solution
     * @param {String} enabler
     */
    enableSolution = function (solution, enabler) {
        setSolutionUsedefaultEnabled(solution, enabler);
        setSolutionPropEnabled(solution, enabler);
        setSolutionSelectEnabled(solution, enabler);
        setSolutionMarkEnabled(solution, enabler);
    },

    /**
     * Lock/unlock solution configuration button
     *
     * @param {*} solution
     * @param {String} buttonConfiguration
     * @param {Boolean} unlock
     */
    setSolutionConfigurationUnlock = function (solution, buttonConfiguration, unlock) {
        var solutionConfiguration = solution.find(buttonConfiguration);

        unlock || typeof unlock === 'undefined' ?
            solutionConfiguration.removeClass('disabled').removeAttr('disabled') :
            solutionConfiguration.addClass('disabled').attr('disabled', 'disabled');
    },

    /**
     * Set/unset solution usedefault checkbox
     *
     * @param {*} solution
     * @param {String} enabler
     * @param {Boolean} checked
     */
    setSolutionUsedefaultEnabled = function (solution, enabler, checked) {
        checked = !(checked || typeof checked === 'undefined');

        solution.find('input[id="' + solution.find(enabler).attr('id') + '_inherit"]')
            .prop('checked', checked);
    },

    /**
     * Forward solution select-enabler changes
     *
     * @param {*} solution
     * @param {String} enabler
     */
    forwardSolutionChange = function (solution, enabler) {
        solution.find(enabler).change();
    },

    /**
     * Show/hide dependent fields
     *
     * @param {*} solution
     * @param {String} identifier
     * @param {Boolean} show
     */
    showDependsField = function (solution, identifier, show) {
        show = show || typeof show === 'undefined';

        solution.find(identifier).toggle(show);
        solution.find(identifier).closest('tr').toggle(show);
        solution.find(identifier).attr('disabled', !show);
    };

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
         * @param {Object} data
         */
        simpleDisable: function ($target, $owner, data) {
            setSolutionSelectEnabled($target, data.enableButton, false);
            setSolutionLabelsMarkEnabled($target, false);
            setSolutionSectionMarkEnabled($target, false);
        },

        /**
         * @param {*} $target
         */
        simpleMarkEnable: function ($target) {
            setSolutionSectionMarkEnabled($target);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        disable: function ($target, $owner, data) {
            this.simpleDisable($target, $owner, data);
            forwardSolutionChange($target, data.enableButton);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalExpressDisable: function ($target, $owner, data) {
            setSolutionSelectEnabled($target, data.enableButton, false);
            setSolutionLabelsMarkEnabled($target, false);
            forwardSolutionChange($target, data.enableButton);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalExpressLockConfiguration: function ($target, $owner, data) {
            setSolutionConfigurationUnlock($target, data.buttonConfiguration, false);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalExpressLockConfigurationConditional: function ($target, $owner, data) {
            if (
                !isSolutionEnabled($target, data.enableInContextPayPal) &&
                hasRelationsEnabled(data)
            ) {
                this.paypalExpressLockConfiguration($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalExpressMarkDisable: function ($target, $owner, data) {
            if (!hasRelationsEnabled(data)) {
                this.simpleDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalExpressUnlockConfiguration: function ($target, $owner, data) {
            if (!hasRelationsEnabled(data)) {
                setSolutionConfigurationUnlock($target, data.buttonConfiguration);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalBmlDisable: function ($target, $owner, data) {
            disableSolution($target, data.enableBmlPayPal);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalBmlDisableConditional: function ($target, $owner, data) {
            if (!isSolutionEnabled($target, data.enableButton)) {
                this.paypalBmlDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalBmlEnable: function ($target, $owner, data) {
            enableSolution($target, data.enableBmlPayPal);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressDisable: function ($target, $owner, data) {
            disableSolution($target, data.enableExpress);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressDisableConditional: function ($target, $owner, data) {
            if (
                !isSolutionEnabled($target, data.enableButton) ||
                hasRelationsEnabled(data)
            ) {
                this.payflowExpressDisable($target, $owner, data);
                forwardSolutionChange($target, data.enableExpress);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressEnable: function ($target, $owner, data) {
            enableSolution($target, data.enableExpress);
            forwardSolutionChange($target, data.enableExpress);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressEnableConditional: function ($target, $owner, data) {
            if (hasRelationsEnabled(data)) {
                setSolutionPropEnabled($target, data.enableExpress, false);
                setSolutionSelectEnabled($target, data.enableExpress);
                setSolutionMarkEnabled($target, data.enableExpress);
            } else {
                disableSolution($target, data.enableExpress);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressLockConditional: function ($target, $owner, data) {
            if (!isSolutionEnabled($target, data.enableButton)) {
                setSolutionPropEnabled($target, data.enableExpress, false);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressUsedefaultDisable: function ($target, $owner, data) {
            setSolutionUsedefaultEnabled($target, data.enableExpress, false);
            this.payflowExpressEnable($target, $owner, data);
            forwardSolutionChange($target, data.enableExpress);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowExpressUsedefaultEnable: function ($target, $owner, data) {
            setSolutionUsedefaultEnabled($target, data.enableExpress);
            this.payflowExpressDisable($target, $owner, data);
            forwardSolutionChange($target, data.enableExpress);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowBmlDisable: function ($target, $owner, data) {
            disableSolution($target, data.enableBml);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowBmlDisableConditional: function ($target, $owner, data) {
            if (
                !isSolutionEnabled($target, data.enableButton) ||
                hasRelationsEnabled(data)
            ) {
                this.payflowBmlDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowBmlDisableConditionalExpress: function ($target, $owner, data) {
            if (!isSolutionEnabled($target, data.enableExpress)) {
                this.payflowBmlDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowBmlEnable: function ($target, $owner, data) {
            enableSolution($target, data.enableBml);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowBmlEnableConditional: function ($target, $owner, data) {
            if (hasRelationsEnabled(data)) {
                setSolutionPropEnabled($target, data.enableBml, false);
                setSolutionSelectEnabled($target, data.enableBml);
                setSolutionMarkEnabled($target, data.enableBml);
            } else {
                disableSolution($target, data.enableBml);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowBmlLockConditional: function ($target, $owner, data) {
            if (!isSolutionEnabled($target, data.enableButton)) {
                setSolutionPropEnabled($target, data.enableBml, false);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextEnable: function ($target, $owner, data) {
            enableSolution($target, data.enableInContextPayPal);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextDisable: function ($target, $owner, data) {
            disableSolution($target, data.enableInContextPayPal);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextShowMerchantId: function ($target, $owner, data) {
            showDependsField($target, data.dependsMerchantId);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextHideMerchantId: function ($target, $owner, data) {
            showDependsField($target, data.dependsMerchantId, false);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowShowSortOrder: function ($target, $owner, data) {
            showDependsField($target, data.dependsBmlSortOrder);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        payflowHideSortOrder: function ($target, $owner, data) {
            showDependsField($target, data.dependsBmlSortOrder, false);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalShowSortOrder: function ($target, $owner, data) {
            showDependsField($target, data.dependsBmlApiSortOrder);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        paypalHideSortOrder: function ($target, $owner, data) {
            showDependsField($target, data.dependsBmlApiSortOrder, false);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextActivate: function ($target, $owner, data) {
            setSolutionMarkEnabled($target, data.enableInContextPayPal);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextDeactivate: function ($target, $owner, data) {
            setSolutionMarkEnabled($target, data.enableInContextPayPal, false);
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        inContextDisableConditional: function ($target, $owner, data) {
            if (!isSolutionEnabled($target, data.enableButton)) {
                this.inContextDisable($target, $owner, data);
            }
        },

        /**
         * @param {*} $target
         * @param {*} $owner
         * @param {Object} data
         */
        conflict: function ($target, $owner, data) {
            var newLine = String.fromCharCode(10, 13);

            if (
                isSolutionEnabled($owner, data.enableButton) &&
                hasRelationsEnabled(data) &&
                !this.executed
            ) {
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
    });
});
