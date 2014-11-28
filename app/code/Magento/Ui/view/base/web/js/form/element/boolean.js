/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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