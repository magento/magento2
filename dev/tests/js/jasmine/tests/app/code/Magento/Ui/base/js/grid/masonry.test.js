/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*eslint max-nested-callbacks: 0*/
define([
    'jquery',
    'ko',
    'Magento_Ui/js/grid/masonry'
], function ($, ko, Masonry) {
    'use strict';

    var Component,
        rows,
        container = '<div data-id="masonry_grid" id="masonry_grid"><div class="masonry-image-column"></div></div>';

    beforeEach(function () {
        rows = [
            {
                _rowIndex: 0,
                category: {},
                'category_id': 695,
                'category_name': 'People',
                'comp_url': 'https://stock.adobe.com/Rest/Libraries/Watermarked/Download/327515738/2',
                'content_type': 'image/jpeg',
                'country_name': 'Malaysia',
                'creation_date': '2020-03-02 10:41:51',
                'creator_id': 208217780,
                'creator_name': 'NajmiArif',
                height: 3264,
                id: 327515738,
                'id_field_name': 'id',
                'is_downloaded': 0,
                'is_licensed_locally': 0,
                keywords: [],
                'media_type_id': 1,
                overlay: '',
                path: '',
                'premium_level_id': 0,
                'thumbnail_240_url': 'https://t4.ftcdn.net/jpg/03/27/51/57/240_F_327515738_n.jpg',
                'thumbnail_500_ur': 'https://as2.ftcdn.net/jpg/03/27/51/57/500_F_327515738_n.jpg',
                title: 'Neon effect picture of man wearing medical mask for viral or pandemic disease',
                width: 4896
            }

        ];

        $(container).appendTo('body');

        Component = new Masonry({
            defaults: {
                rows: ko.observable()
            }
        });

    });

    afterEach(function () {
        $('#masonry_grid').remove();
    });

    describe('check initComponent', function () {
        it('verify setLayoutstyles called and grid iniztilized', function () {
            var setlayoutStyles = spyOn(Component, 'setLayoutStyles');

            expect(Component).toBeDefined();
            Component.containerId = 'masonry_grid';
            Component.initComponent(rows);
            Component.rows().forEach(function (image) {
                expect(image.styles).toBeDefined();
                expect(image.css).toBeDefined();
            });
            expect(setlayoutStyles).toHaveBeenCalled();
        });
        it('verify events triggered', function () {
            var setLayoutStyles = spyOn(Component, 'setLayoutStyles');

            Component.initComponent(rows);
            window.dispatchEvent(new Event('resize'));
            expect(setLayoutStyles).toHaveBeenCalled();
        });
    });
});
