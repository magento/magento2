/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @deprecated since version 2.2.0
 */
/* eslint-disable strict */
define(['jquery'], function ($) {
    return function () {
        var renderedChildren = {},
            children = {};

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
