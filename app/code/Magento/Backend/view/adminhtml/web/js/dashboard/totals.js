/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */

/*global FORM_KEY*/
define([
    'jquery',
    'jquery-ui-modules/widget'
], function ($) {
    'use strict';

    $.widget('mage.dashboardTotals', {
        options: {
            updateUrl: '',
            periodSelect: null
        },
        elementId: null,

        /**
         * @private
         */
        _create: function () {
            this.elementId = $(this.element).attr('id');

            if (this.options.periodSelect) {
                $(document).on('change', this.options.periodSelect, $.proxy(function () {
                    this.refreshTotals();
                }, this));
            }
        },

        /**
         * @public
         */
        refreshTotals: function () {
            var periodParam = '';

            if (this.options.periodSelect && $(this.options.periodSelect).val()) {
                periodParam = 'period/' + $(this.options.periodSelect).val() + '/';
            }

            $.ajax({
                url: this.options.updateUrl + periodParam,
                showLoader: true,
                data: {
                    'form_key': FORM_KEY
                },
                dataType: 'html',
                type: 'POST',
                success: $.proxy(function (response) {
                    $('#' + this.elementId).replaceWith(response);
                }, this)
            });
        }
    });

    return $.mage.dashboardTotals;
});
