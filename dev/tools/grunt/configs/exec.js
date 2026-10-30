/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */

'use strict';

var combo = require('./combo'),
    themes = require('../tools/files-router').get('themes'),
    _      = require('underscore');

var themeOptions = {};

_.each(themes, function(theme, name) {
    themeOptions[name] = {
        cmd: combo.collector.bind(combo, name)
    };
});

var execOptions = {
    all : {
        cmd: function () {
            var cmdPlus = (/^win/.test(process.platform) == true) ? ' & ' : ' && ',
                command;

            command = _.map(themes, function(theme, name) {
                return combo.collector(name);
            }).join(cmdPlus);

            return 'echo ' + command;
        }
    }
};

/**
 * Execution into cmd
 */
module.exports = _.extend(themeOptions, execOptions);
