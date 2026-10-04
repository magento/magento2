/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/url'
], function ($, urlBuilder) {
    'use strict';

    return function (data) {
        $.ajax({
            url: urlBuilder.build('reports/report_product/view'),
            type: 'POST',
            data: {'product_id': data.product_id },
            dataType: 'json'
        });
    };
});
