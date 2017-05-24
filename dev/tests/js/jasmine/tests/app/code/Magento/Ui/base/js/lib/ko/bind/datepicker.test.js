/**
 * Copyright © Magento, Inc. All rights reserved.
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
        var observable,
            element,
            config;

        beforeEach(function () {
            element = $('<input />');
            observable = ko.observable();

            config = {
                options: {
                    dateFormat: 'M/d/yy',
                    storeLocale: 'en_US',
                    timeFormat: 'h:mm: a'
                },
                storage: observable
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
            var todayDate, momentFormat, result,
                inputFormat = 'M/d/yy';

            momentFormat = utils.convertToMomentFormat(inputFormat);
            todayDate = moment().format(momentFormat);

            element.datepicker('setTimezoneDate').blur().trigger('change');
            result = moment(observable()).format(momentFormat);

            expect(todayDate).toEqual(result);
        });
    });
});
