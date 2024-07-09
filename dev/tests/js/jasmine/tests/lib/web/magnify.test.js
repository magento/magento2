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
        var gallery, options, magnify;

        beforeEach(function () {
            options = {
                options: {
                    "nav":"thumbs","loop":true,"keyboard":true,"arrows":true,"allowfullscreen":true,
                    "showCaption":false,"width":700,"thumbwidth":88,"thumbheight":110,"height":700,
                    "transitionduration":500,"transition":"slide","navarrows":true,"navtype":"slides",
                    "navdir":"horizontal","whiteBorders":1
                },
                fullscreen: {
                    "nav":"thumbs","loop":true,"navdir":"horizontal","navarrows":false,"navtype":"slides",
                    "arrows":true,"showCaption":false,"transitionduration":500,"transition":"slide","whiteBorders":1
                },
                breakpoints: {"mobile":{"conditions":{"max-width":"767px"},"options":{"options":{"nav":"dots"}}}},
                data: [
                    {
                        "thumb":"/images/sample.jpg",
                        "img":"/images/sample.jpg",
                        "full":"/images/sample.jpg",
                        "caption":"simple1","position":"0","isMain":false,"type":"image","videoUrl":null
                    }
                    ],
                magnifierOpts: {
                    "fullscreenzoom":"20",
                    "top":"",
                    "left":"",
                    "width":"",
                    "height":"",
                    "eventType":"hover",
                    "enabled":false,
                    "mode":"outside"
                }
            };
        });

        describe('test magnifierFullscreen method', function () {
            it('Check if the current image has event handlers set for tap and double tap', function () {
                let productMediaDiv = document.createElement('div');
                productMediaDiv.className = 'product media';

                let mainDiv = document.createElement('div');
                mainDiv.id = 'gallery_placeholder';
                mainDiv.className = 'gallery-placeholder';
                mainDiv.setAttribute('data-gallery-role', 'gallery-placeholder');

                let img = document.createElement('img');
                img.alt = 'main product photo';
                img.id = 'main_product_photo';
                img.className = 'gallery-placeholder__image';
                img.src = '/images/sample.jpg';
                mainDiv.appendChild(img);
                productMediaDiv.appendChild(mainDiv);
                document.body.appendChild(productMediaDiv);

                magnify = new Magnify(options, $('#gallery_placeholder'));
                gallery = new Gallery(options, $('#gallery_placeholder'));
                expect(gallery.settings.fullscreenConfig).toBeDefined();
                expect(gallery.settings.fotoramaApi).toBeDefined();
                expect(gallery.settings.data).toBeDefined();
                expect(gallery.settings.api).toBeDefined();

                gallery.openFullScreen();

                console.log($('.fotorama__img--full')); //this should be available
                //console.log($._data($('.fotorama__img--full')[0], "events"));
            });
        });
    });
});
