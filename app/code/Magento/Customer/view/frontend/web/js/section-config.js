/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define(['underscore'], function (_) {
    'use strict';

    var baseUrls = [],
        sections = [],
        clientSideSections = [],
        sectionNames = [],
        canonize;

    /**
     * @param {String} url
     * @return {String}
     */
    canonize = function (url) {
        var route = url;

        _.some(baseUrls, function (baseUrl) {
            route = url.replace(baseUrl, '');

            return route !== url;
        });

        return route.replace(/^\/?index.php\/?/, '').toLowerCase();
    };

    return {
        /**
         * Returns a list of sections which should be invalidated for given URL.
         * @param {String} url - URL which was requested.
         * @return {Array} - List of sections to invalidate.
         */
        getAffectedSections: function (url) {
            var route = canonize(url),
                actions = _.find(sections, function (val, section) {
                    var matched;

                    // Covers the case where "*" works as a glob pattern.
                    if (section.indexOf('*') >= 0) {
                        section = section.replace(/\*/g, '[^/]+') + '$';
                        matched = route.match(section);

                        return matched && matched[0] === route;
                    }

                    return route.indexOf(section) === 0;
                });

            return _.union(_.toArray(actions), _.toArray(sections['*']));
        },

        /**
         * Filters the list of given sections to the ones defined as client side.
         * @param {Array} allSections - List of sections to check.
         * @return {Array} - List of filtered sections.
         */
        filterClientSideSections: function (allSections) {
            return _.difference(_.toArray(allSections), clientSideSections);
        },

        /**
         * Tells if section is defined as client side.
         * @param {String} sectionName - Name of the section to check.
         * @return {Boolean}
         */
        isClientSideSection: function (sectionName) {
            return _.contains(clientSideSections, sectionName);
        },

        /**
         * Returns array of section names.
         * @returns {Array}
         */
        getSectionNames: function () {
            return sectionNames;
        },

        /**
         * @param {Object} options
         * @constructor
         */
        'Magento_Customer/js/section-config': function (options) {
            baseUrls = options.baseUrls;
            sections = options.sections;
            clientSideSections = options.clientSideSections;
            sectionNames = options.sectionNames;
        }
    };
});
