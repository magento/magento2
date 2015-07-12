/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'moment',
    'Magento_Ui/js/lib/ko/bind/datepicker'
], function (ko, $, moment) {
    'use strict';

    describe('Datepicker binding', function () {
        var observable,
            element;

        beforeEach(function () {
            element    = $('<input />');
            observable = ko.observable();

            $(document.body).append(element);

            ko.applyBindingsToNode(element[0], { datepicker: observable });
        });

        afterEach(function () {
            element.remove();
        });

        it('writes picked date\'s value to assigned observable', function () {
            var openBtn,
                todayBtn,
                todayDate,
                dateFormat,
                result;

            dateFormat  = element.datepicker('option', 'dateFormat');
            todayDate   = moment().format(dateFormat);

            openBtn  = $('img.ui-datepicker-trigger');
            todayBtn = $('[data-handler="today"]');

            openBtn.click();
            todayBtn.click();

            result = moment(observable()).format(dateFormat);

            expect(todayDate).toEqual(result);
        });
    });
});