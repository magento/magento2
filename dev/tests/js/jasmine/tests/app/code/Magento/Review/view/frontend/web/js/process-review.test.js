/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
/*jscs:disable jsDoc*/

define([
    'jquery',
    'Magento_Review/js/process-reviews'
], function ($, reviewProcessor) {
    'use strict';

    describe('Test product page reviews processor', function () {
        var element,
        config = {
            reviewsTabSelector: '#review-tab'
        };

        beforeEach(function () {
            element = $('<div id="review-tab" role="tab"></div>');

            $('body').append(element);
        });

        afterEach(function () {
            element.remove();
        });

        it('Should automatically load reviews after page load if review tab is active', function () {
            element.addClass('active');

            spyOn($, 'ajax').and.callFake(function () {
                var d = $.Deferred();

                d.promise().complete = function () {};

                return d.promise();
            });

            reviewProcessor(config, null);

            expect($.ajax).toHaveBeenCalled();
        });

        it('Should not automatically load reviews after page load if review tab is not active', function () {
            spyOn($, 'ajax').and.callFake(function () {
                var d = $.Deferred();

                d.promise().complete = function () {};

                return d.promise();
            });

            reviewProcessor(config, null);

            expect($.ajax).not.toHaveBeenCalled();
        });

        it('Should load reviews if non active review tab was opened', function () {
            spyOn($, 'ajax').and.callFake(function () {
                var d = $.Deferred();

                d.promise().complete = function () {};

                return d.promise();
            });

            reviewProcessor(config, null);
            element.trigger('beforeOpen');

            expect($.ajax).toHaveBeenCalled();
        });
    });
});
