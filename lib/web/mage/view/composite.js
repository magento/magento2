/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(['jquery'], function($) {
    return function (wrapperTag) {
        wrapperTag = wrapperTag || 'div';
        var renderedChildren = {};
        var children = {};
        return {
            addChild: function (child, key) {
                children[key] = child;
            },

            render: function (root) {
                $.each(children, function (key, child) {
                    var childRoot = $('<div>');
                    renderedChildren[key] = child.render(childRoot);
                    root.append(childRoot);
                });
            }
        }
    }
});
