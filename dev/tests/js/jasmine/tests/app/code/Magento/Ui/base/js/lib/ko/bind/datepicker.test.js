/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'moment',
    'mageUtils',
    'mage/calendar',
    'Magento_Ui/js/lib/knockout/bindings/datepicker'
], function (ko, $, moment, utils) {
    'use strict';

    describe('Datepicker binding', function () {
        var observable,
            element,
            config;

        beforeEach(function () {
            jasmine.clock().install();
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
            jasmine.clock().uninstall();
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

        it('update picked date\'s value after update observable value', function () {
            var date = '06/21/2019',
                inputFormat = 'M/d/yy',
                expectedDate;

            expectedDate = moment(date, utils.convertToMomentFormat(inputFormat)).toDate();
            observable(date);

            jasmine.clock().tick(100);

            expect(expectedDate.valueOf()).toEqual(element.datepicker('getDate').valueOf());
        });

        it('clear picked date\'s value after clear observable value', function () {
            element.datepicker('setTimezoneDate').blur().trigger('change');
            observable('');

            jasmine.clock().tick(100);

            expect(null).toEqual(element.datepicker('getDate'));
        });
    });
});
