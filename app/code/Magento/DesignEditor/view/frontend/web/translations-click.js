/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mageUtils',
    'mage/translate-inline',
    'mage/translate-inline-vde'
], function ($, utils) {
    'use strict';

    return function (config, element) {

        return utils.extend(config, {

            /**
             * Extended handler for
             * @param {jQuery.Event} event
             * @param {Object} widget
             */
            onClick: function (event, widget) {
                $('body').removeClass('trnslate-inline-' + widget.options.translateMode + '-area');
                $(element)[config.translation]('hide');
                $('#translate-dialog').translateInlineDialogVde(
                    'openWithWidget',
                    event,
                    widget,
                    $.proxy(widget.replaceText, widget)
                );
            }
        });
    };
});
