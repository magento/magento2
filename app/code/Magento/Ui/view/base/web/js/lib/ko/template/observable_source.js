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
/**
 * Is being used by knockout template engine to store template to.
 */
define(['ko', 'Magento_Ui/js/lib/class'], function(ko, Class) {
    'use strict';

    return Class.extend({

        /**
         * Initializes templateName, _data, nodes properties.
         * @param  {template} template - identifier of template
         */
        initialize: function(template) {
            this.templateName = template;
            this._data = {};
            this.nodes = ko.observable([]);
        },

        /**
         * Data setter. If only one arguments passed, returns corresponding value.
         * Else, writes into it.
         * @param  {String} key - key to write to or to read from
         * @param  {*} value
         * @return {*} - if 1 arg provided, returnes _data[key] property
         */
        data: function(key, value) {
            if (arguments.length === 1) {
                return this._data[key];
            }

            this._data[key] = value;
        }
    });
});