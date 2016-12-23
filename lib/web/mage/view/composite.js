/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['jquery'], function ($) {
    'use strict';

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
