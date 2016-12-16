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
        var observable,
            element;

        beforeEach(function () {
            element    = $('<input />');
            observable = ko.observable();

            $(document.body).append(element);

            ko.applyBindingsToNode(element[0], {
                datepicker: observable
            });
        });

        afterEach(function () {
            element.remove();
        });

        it('writes picked date\'s value to assigned observable', function () {
            var openBtn,
                todayBtn,
                todayDate,
                result,
                inputFormat,
                momentFormat;

            inputFormat = 'M/d/yy';
            momentFormat = utils.convertToMomentFormat(inputFormat);
            todayDate   = moment().format(momentFormat);

            openBtn  = $('img.ui-datepicker-trigger');
            todayBtn = $('[data-handler="today"]');

            openBtn.click();
            todayBtn.click();

            result = moment(observable()).format(momentFormat);

            expect(todayDate).toEqual(result);
        });
    });
});
