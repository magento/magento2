/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'fotorama/fotorama',
    'text!mage/gallery/gallery.html'
], function ($, fotorama, template) {
    'use strict';

    return function (config, element) {
        var api = {},
            $fotoramaItem,
            $element = $(element);

        $element.html(template);
        $fotoramaItem = $($element.children(0));

        if ('ontouchstart' in document.documentElement) {
            config.arrows = false;
            config.nav = 'dots';
        }
        $fotoramaItem.fotorama(config);

        api.fotorama = $fotoramaItem.data('fotorama');

        /**
         * Displays the last image on preview.
         */
        api.last = function() {
            this.fotorama.show('>>');
        };

        /**
         * Displays the first image on preview.
         */
        api.first = function() {
            this.fotorama.show('<<');
        };

        /**
         * Displays previous element on preview.
         */
        api.prev = function() {
            this.fotorama.show('<');
        };

        /**
         * Displays next element on preview.
         */
        api.next = function() {
            this.fotorama.show('>');
        };

        /**
         * Displays image with appropriate count number on preview. 
         * @param {number} index -Number of image that should be displayed.
         */
        api.seek = function(index) {
            this.fotorama.show(index - 1);
        };

        /**
         * Updates gallery with new set of options.
         * @param {object} config - Standart gallery configuration object.
         */
        api.updateOptions = function(config) {

            if ('ontouchstart' in document.documentElement) {
                config.arrows = false;
                config.nav = 'dots';
            }
            this.fotorama.setOptions(config);
        };

        /**
         * Updates gallery with specific set of items.
         * @param {Array.<Object>} data - Set of gallery items to update.
         */
        api.updateData = function(data) {
            this.fotorama.load(data);
        };

        $fotoramaItem.data("gallery", api);
        $fotoramaItem.trigger("gallery:loaded");
    };
});
