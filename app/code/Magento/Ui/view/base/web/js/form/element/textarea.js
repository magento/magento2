/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/**
 * @api
 */
define([
    './abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            cols: 15,
            rows: 2,
            elementTmpl: 'ui/form/element/textarea'
        }
    });
});
