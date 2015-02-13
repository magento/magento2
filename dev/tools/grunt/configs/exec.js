'use strict';

var combo = require('./combo'),
    theme = require('./theme');

/**
 * Execution into cmd
 */
module.exports = {
    blank: {
        cmd: function () {
            return combo.collector('blank');
        }
    },
    luma: {
        cmd: function () {
            return combo.collector('luma');
        }
    },
    backend: {
        cmd: function () {
            return combo.collector('backend');
        }
    },
    all: {
        cmd: function () {
            var command = '',
                cmdPlus = /^win/.test(process.platform) ? ' & ' : ' && ',
                themes = Object.keys(theme),
                i = 0;

            for (i; i < themes.length; i++) {
                command += combo.collector(themes[i]) + cmdPlus;
            }

            return 'echo ' + command;
        }
    }
};
