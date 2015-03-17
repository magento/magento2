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

    function modify(initial, raw) {
        raw = raw || initial;

        _.each(raw, function (value, key) {
            if (isTemplate(key)) {
                delete raw[key];

                key = render(key, initial);
                raw[key] = value;
            }

            if (isTemplate(value)) {
                value = render(value, initial);
                raw[key] = value;
            }

            if (typeof value === 'object') {
                modify(initial, value);
            }
        });
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

            modify(tmpl);

            delete tmpl.$data;

            return tmpl;
        }
    };
});
