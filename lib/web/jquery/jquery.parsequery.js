/**
 * Copyright (c) 2010 Conrad Irwin <conrad@rapportive.com> MIT license.
 * Based loosely on original: Copyright (c) 2008 mkmanning MIT license.
 *
 * Parses CGI query strings into javascript objects.
 *
 * See the README for details.
 **/
define([
    "jquery"
], function($){
    $.parseQuery = function (options) {

        var config = {query: window.location.search || ""},
            params = {};

        if (typeof options === 'string') {
            options = {query: options};
        }
        $.extend(config, $.parseQuery, options);
        config.query = config.query.replace(/^\?/, '');

        if (config.query.length > 0) {
            $.each(config.query.split(config.separator), function (i, param) {
                var pair = param.split('='),
                    key = config.decode(pair.shift(), null).toString(),
                    value = config.decode(pair.length ? pair.join('=') : null, key);

                if (config.array_keys.test ? config.array_keys.test(key) : config.array_keys(key)) {
                    params[key] = params[key] || [];
                    params[key].push(value);
                } else {
                    params[key] = value;
                }
            });
        }
        return params;
    };
    $.parseQuery.decode = $.parseQuery.default_decode = function (string) {
        return decodeURIComponent((string || "").replace(/\+/g, ' '));
    };
    $.parseQuery.array_keys = function () {
        return false;
    };
    $.parseQuery.separator = "&";
});
