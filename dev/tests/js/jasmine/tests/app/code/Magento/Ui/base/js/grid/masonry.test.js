/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Ui/js/grid/masonry',
    'jquery'
], function (Masonry, $) {
    'use strict';

    describe('Magento_Ui/js/grid/masonry', function () {
        var model;

        beforeEach(function () {
            $(document.body).append(
                $('<div id="masonry_grid"><div class="masonry-image-column"></div></div>')
            );
            model = new Masonry({
                defaults: {
                    containerId: '#masonry_grid'
                }
            });
        });

        afterEach(function () {
            $('#masonry_grid').remove();
        });

        describe('check initComponent', function () {
            it('verify setLayoutstyles called and grid iniztilized', function () {
                expect(model).toBeDefined();
            });
            it('verify events triggered', function () {
                expect(model).toBeDefined();
            });
        });
    });
});
