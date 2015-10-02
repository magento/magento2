/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui",
    "domReady!"
], function ($) {
    return function(configFromPage, element) {
        var url = configFromPage.url;
        $.ajax({
            method: 'get',
            url: url
        }).done(function(data) {
            $("div[data-role='partners-block']").html(data);
        });
    }
});