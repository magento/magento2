/*************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * ***********************
 */

/*eslint max-nested-callbacks: 0*/
define(['magnifier/magnify', 'mage/gallery/gallery', 'jquery'], function (Magnify, Gallery, $) {
    'use strict';

    describe('magnifier/magnify', function () {
        var gallery, options;

        beforeEach(function () {
            options = {
                options: {
                    'nav':'thumbs','loop':true,'keyboard':true,'arrows':true,'allowfullscreen':true,
                    'showCaption':false,'width':700,'thumbwidth':88,'thumbheight':110,'height':700,
                    'transitionduration':500,'transition':'slide','navarrows':true,'navtype':'slides',
                    'navdir':'horizontal','whiteBorders':1
                },
                fullscreen: {
                    'nav':'thumbs','loop':true,'navdir':'horizontal','navarrows':false,'navtype':'slides',
                    'arrows':true,'showCaption':false,'transitionduration':500,'transition':'slide','whiteBorders':1
                },
                breakpoints: {'mobile':{'conditions':{'max-width':'767px'},'options':{'options':{'nav':'dots'}}}},
                data: [
                    {
                        'thumb':'dev/tests/acceptance/tests/_data/adobe-base.jpg',
                        'img':'dev/tests/acceptance/tests/_data/adobe-base.jpg',
                        'full':'dev/tests/acceptance/tests/_data/adobe-base.jpg',
                        'caption':'simple1','position':'0','isMain':false,'type':'image','videoUrl':null
                    }
                ],
                magnifierOpts: {
                    'fullscreenzoom':'20',
                    'top':'',
                    'left':'',
                    'width':'',
                    'height':'',
                    'eventType':'hover',
                    'enabled':false,
                    'mode':'outside'
                }
            };
        });

        describe('test magnifierFullscreen method', function () {
            it('Check if the current image has event handlers set for tap and double tap', function () {
                let pageWrapperDiv, productMediaDiv, mainDiv, img, activeImage, imageEvents;

                pageWrapperDiv = document.createElement('div');
                pageWrapperDiv.className = 'page-wrapper';

                productMediaDiv = document.createElement('div');
                productMediaDiv.className = 'product media';

                mainDiv = document.createElement('div');
                mainDiv.id = 'gallery_placeholder';
                mainDiv.className = 'gallery-placeholder _block-content-loading';
                mainDiv.setAttribute('data-gallery-role', 'gallery-placeholder');

                img = document.createElement('img');
                img.id = 'main_product_photo';
                img.className = 'gallery-placeholder__image';
                img.src = 'dev/tests/acceptance/tests/_data/adobe-base.jpg';

                mainDiv.appendChild(img);
                pageWrapperDiv.appendChild(productMediaDiv);
                productMediaDiv.appendChild(mainDiv);
                document.body.appendChild(pageWrapperDiv);

                new Magnify(options, $('#gallery_placeholder'));
                gallery = new Gallery(options, $('#gallery_placeholder'));

                activeImage = document.createElement('img');
                activeImage.className = 'fotorama__img--full';
                $('[data-gallery-role="stage-shaft"] [data-active="true"]').append(activeImage);

                gallery.openFullScreen();

                imageEvents = $._data($('.fotorama__img--full')[0], 'events');
                expect(imageEvents).toBeInstanceOf(Object);
                expect(Object.getOwnPropertyNames(imageEvents)).toContain('touchend');
                expect(Object.getOwnPropertyNames(imageEvents)).toContain('pointerdown');
                expect(Object.getOwnPropertyNames(imageEvents)).toContain('pointermove');
                expect(Object.getOwnPropertyNames(imageEvents)).toContain('pointerup');
                expect(Object.getOwnPropertyNames(imageEvents)).toContain('pointercancel');
                expect(Object.getOwnPropertyNames(imageEvents)).toContain('pointerout');
            });
        });
    });
});
