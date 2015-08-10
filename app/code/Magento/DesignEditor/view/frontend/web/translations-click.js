/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jQuery',
    'mageUtils',
    "mage/translate-inline",
    "mage/translate-inline-vde"
], function($,utils){
    'use strict';

    return function (config, element) {

        return utils.extend(config, {
            onClick: function(e, widget) {
                $('body').removeClass('trnslate-inline-' + widget.options.translateMode + '-area');
                $(element)[config.translation]('hide');
                $('#translate-dialog').translateInlineDialogVde(
                    'openWithWidget',
                    e,
                    widget,
                    $.proxy(widget.replaceText, widget)
                );
            }
        });
    };
});
