/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    './abstract'
], function (Abstract) {
    'use strict';

    var defaults = {
        cols: 15,
        rows: 2,
        template: 'ui/form/element/textarea'
    };

    return Abstract.extend({

        /**
         * Invokes initialize method of parent class.
         */
        initialize: function (config) {
            _.extend(this, defaults);
            
            Abstract.prototype.initialize.apply(this, arguments);
        }
    });
});