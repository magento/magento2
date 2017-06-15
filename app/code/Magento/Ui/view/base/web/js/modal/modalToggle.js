/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'modal'
], function ($) {
    'use strict';

    return function (config, el) {
        var widget,
            content;

        if (config.contentSelector) {
            content = $(config.contentSelector);
        } else if (config.content) {
            content = $('<div />').html(config.content);
        } else {
            content = $('<div />');
        }

        widget = content.modal(config);

        $(el).on(config.toggleEvent, function () {
            var state = widget.data('mage-modal').options.isOpen;

            if (state) {
                widget.modal('closeModal');
            } else {
                widget.modal('openModal');
            }

            return false;
        });

        return widget;
    };
});
