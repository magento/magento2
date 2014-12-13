/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
define([
    './abstract'
], function (Abstract) {
    'use strict';

    var __super__ = Abstract.prototype;

    return Abstract.extend({
        /**
         * Converts the result of parent 'getInitialValue' call to boolean
         * 
         * @return {Boolean}
         */
        getInititalValue: function(){
            var value = __super__.getInititalValue.apply(this, arguments);

            return !!+value;
        },

        /**
         * Calls 'store' method of parent, if value is defined and instance's
         *     'unique' property set to true, calls 'setUnique' method
         *     
         * @param  {*} value
         * @return {Object} - reference to instance
         */
        store: function() {
            __super__.store.apply(this, arguments);

            if (this.hasUnique) {
                this.setUnique();
            }

            return this;
        }
    });
});