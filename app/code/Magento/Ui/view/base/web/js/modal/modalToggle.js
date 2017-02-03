/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function($){
    'use strict';

    return function(config, el) {
        var widget = $(config.content).modal(config);

        $(el).on(config.toggleEvent, function() {
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
