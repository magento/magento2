/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['underscore'], function (_) {
    'use strict';

    var baseUrls, sections, clientSideSections, canonize;

    /**
     * @param {String} url
     * @return {String}
     */
    canonize = function (url) {
        var route = url,
            key;

        for (key in baseUrls) { //eslint-disable-line guard-for-in
            route = url.replace(baseUrls[key], '');

            if (route != url) { //eslint-disable-line eqeqeq
                break;
            }
        }

        return route.replace(/^\/?index.php\/?/, '').toLowerCase();
    };

    return {
        /**
         * @param {String} url
         * @return {Array}
         */
        getAffectedSections: function (url) {
            var route = canonize(url),
                actions = _.find(sections, function (val, section) {
                    var matched;

                    if (section.indexOf('*') >= 0) {
                        section = section.replace(/\*/g, '[^/]+') + '$';
                        matched = route.match(section);

                        return matched && matched[0] == route; //eslint-disable-line eqeqeq
                    }

                    return route.indexOf(section) === 0;
                });

            return _.union(_.toArray(actions), _.toArray(sections['*']));
        },

        /**
         * @param {*} allSections
         * @return {*}
         */
        filterClientSideSections: function (allSections) {
            if (Array.isArray(allSections)) {
                return _.difference(allSections, clientSideSections);
            }

            return allSections;
        },

        /**
         * @param {String} sectionName
         * @return {Boolean}
         */
        isClientSideSection: function (sectionName) {
            return _.contains(clientSideSections, sectionName);
        },

        /**
         * @param {Object} options
         * @constructor
         */
        'Magento_Customer/js/section-config': function (options) {
            baseUrls = options.baseUrls;
            sections = options.sections;
            clientSideSections = options.clientSideSections;
        }
    };
});
