/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'moment',
    'mageUtils',
    'Magento_Ui/js/lib/knockout/bindings/datepicker'
], function (ko, $, moment, utils) {
    'use strict';

    describe('Datepicker binding', function () {
        var element,
            config;

        beforeEach(function () {
            element    = $('<input />');

            config = {
                options: {
                    dateFormat: 'M/d/yy',
                    'storeLocale': 'en_US',
                    'timeFormat': 'h:mm: a'
                },
                storage: ko.observable(moment().format('MM/DD/YYYY'))
            };

            $(document.body).append(element);

            ko.applyBindingsToNode(element[0], {
                datepicker: config
            });
        });

        afterEach(function () {
            element.remove();
        });

        it('writes picked date\'s value to assigned observable', function () {
            var todayDate,
                momentFormat,
                result,
                inputFormat;

            inputFormat = 'M/d/yy';

            momentFormat = utils.convertToMomentFormat(inputFormat);

            todayDate   = moment().format(momentFormat);

            result = $('input:last').val();

            expect(todayDate).toEqual(result);
        });
    });
});
