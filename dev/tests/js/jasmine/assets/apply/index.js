/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
define([
    'tests/assets/tools',
    'tests/assets/apply/components/fn',
    'text!./config.json',
    'text!./templates/node.html'
], function (tools, fnComponent, config, nodeTmpl) {
    'use strict';

    var preset;

    preset = tools.init(config, {
        'fn': nodeTmpl
    });

    preset.fn.component = fnComponent;

    return preset;
});
