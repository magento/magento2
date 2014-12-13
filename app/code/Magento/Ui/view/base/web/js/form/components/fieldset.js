/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    'underscore',
    './collapsible'
], function(_, Collapsible) {
    'use strict';

    var defaults = {
        template: 'ui/fieldset/fieldset'
    };

    var __super__ = Collapsible.prototype;

    return Collapsible.extend({

        /**
         * Extends instance with default config, binds required methods
         *     to instance, calls initialize method of parent class.
         */
        initialize: function() {
            _.extend(this, defaults);
            
            __super__.initialize.apply(this, arguments);
        }
    });
});