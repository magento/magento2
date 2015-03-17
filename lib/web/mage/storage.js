/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint regexdash:true eqnull:true browser:true jquery:true*/
define(['jquery', 'mage/url'], function ($, urlBuilder) {
    return {
        get: function (url, data) {
            return $.get(urlBuilder.build(url), data);
        },
        post: function(url, data, contentType) {
            contentType = contentType || 'application/json';
            return $.ajax({
                url: urlBuilder.build(url),
                type: 'POST',
                data: data,
                contentType: contentType
            });
        },
        put: function(url, data, contentType) {
            contentType = contentType || 'application/json';
            return $.ajax({
                url: urlBuilder.build(url),
                type: 'PUT',
                data: data,
                contentType: contentType
            });
        }
    }
});
