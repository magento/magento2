/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable strict */
define(['jquery'], function ($) {
    return function (wrapperTag) {
        var renderedChildren = {},
            children = {};

        wrapperTag = wrapperTag || 'div';

        return {
            /**
             * @param {*} child
             * @param {String} key
             */
            addChild: function (child, key) {
                children[key] = child;
            },

            /**
             * @param {*} root
             */
            render: function (root) {
                $.each(children, function (key, child) {
                    var childRoot = $('<div>');

                    renderedChildren[key] = child.render(childRoot);
                    root.append(childRoot);
                });
            }
        };
    };
});
