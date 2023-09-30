/************************************************************************
 *
 * ADOBE CONFIDENTIAL
 * ___________________
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */

define([
    'jquery'
], function ($) {
    'use strict';

    return function (gallery) {
        return gallery.extend({
            initialize: function (config, element) {
                this._super(config, element);
                $(element).one('f:load', function (event) {
                    if ($(event.target).hasClass('fotorama__active')) {
                        let metaImg = document.createElement('meta');

                        $(metaImg).attr('itemprop', 'image');
                        $(metaImg).attr('content', $(event.target).find('img').attr('src'));
                        $(event.target).append(metaImg);
                        $(event.target).find('img').attr('itemprop', 'image');
                    }
                });
            }
        });
    };
});
