/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/utils/objects'
], function (_, utils) {
    'use strict';

    var opener = '<%';

    function isTemplate(value) {
        return _.isString(value) && ~value.indexOf(opener);
    }

    function render(tmpl, data) {
        var last = tmpl;

        data = Object.create(data);

        while (~tmpl.indexOf(opener)) {
            tmpl = _.template(tmpl)(data);

            if (tmpl === last) {
                break;
            }

            last = tmpl;
        }

        return tmpl;
    }

    return {
        /**
         * Applies provided data to the template.
         *
         * @param {Object} tmpl
         * @param {Object} [$data] - Data object to match with template.
         * @returns {Object}
         *
         * @example Template defined as a string.
         *      var source = { foo: 'Random Stuff', bar: 'Some' };
         *
         *      utils.template('{bar} {foo}', source);
         *      => 'Some Random Stuff';
         *
         * @example Example of template defined as object.
         *      var tpl = { key: { '{bar}_Baz': '{foo}' } };
         *
         *      utils.template(tpl, source);
         *      => { key: { 'Some_Baz': 'Random Stuff' } };
         */
        template: function (tmpl, $data) {
            tmpl = utils.extend({}, tmpl);

            tmpl.$data = $data || {};

            _.each(tmpl, function iterate(value, key, list) {
                if (key === '$data') {
                    return;
                }

                if (isTemplate(key)) {
                    delete list[key];

                    key = render(key, tmpl);
                    list[key] = value;
                }

                if (isTemplate(value)) {
                    list[key] = render(value, tmpl);
                } else if (_.isObject(value)) {
                    _.each(value, iterate);
                }
            });

            delete tmpl.$data;

            return tmpl;
        }
    };
});
