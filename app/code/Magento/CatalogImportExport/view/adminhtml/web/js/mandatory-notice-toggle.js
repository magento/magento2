/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config, element) {
        var $notice = $(element), $entityField = $(config.entitySelector),
            toggleMandatoryAttributesNotice = function (entityValue) {
                if (entityValue === 'catalog_product') {
                    $notice.show();
                } else {
                    $notice.hide();
                }
            };

        toggleMandatoryAttributesNotice($entityField.val());

        $entityField.on('change', function () {
            toggleMandatoryAttributesNotice($(this).val());
        });
    };
});
