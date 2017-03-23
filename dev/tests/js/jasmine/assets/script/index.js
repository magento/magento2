/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'tests/assets/tools',
    'text!./config.json',
    'text!./templates/selector.html',
    'text!./templates/virtual.html'
], function (tools, config, selectorTmpl, virtualTmpl) {
    'use strict';

    return tools.init(config, {
        bySelector: selectorTmpl,
        virtual: virtualTmpl
    });
});
