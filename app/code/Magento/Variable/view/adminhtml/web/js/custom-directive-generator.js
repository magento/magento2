/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

define([
    'underscore'
], function (_) {
    'use strict';

    return _.extend({
        directiveTemplate: '{{customVar code=%s}}',

        /**
         * @param {String} path
         * @return {String}
         */
        processConfig: function (path) {
            return this.directiveTemplate.replace('%s', path);
        }

    });

});
