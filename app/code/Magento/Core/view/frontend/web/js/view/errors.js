/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'text!./templates/errors.html',
        '../model/errorlist'
    ],
    function($, template, errorlist) {
        var list = $('<ul class="messages">', {'id': 'errors'});
        wrapped = errorlist.add;
        errorlist.add = function(error) {
            wrapped(error);
            list.append('<li class="error">' + error + '</li>');
        }
        return {
            render: function (root) {
                root.append(list);
            }
        }
    }
);
