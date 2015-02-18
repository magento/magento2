define([
    'tests/tools',
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
