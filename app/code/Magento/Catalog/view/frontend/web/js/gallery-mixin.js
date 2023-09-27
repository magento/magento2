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
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (initialize) {
        // eslint-disable-next-line no-shadow
        return wrapper.wrap(initialize, function (initialize, config, element) {
            initialize(config, element);
            $(element).one('f:load', function (event) {
                if ($(event.target).hasClass('fotorama__active')) {
                    $(event.target).find('img').attr('itemprop', 'image');
                }
            });
        });
    };
});
