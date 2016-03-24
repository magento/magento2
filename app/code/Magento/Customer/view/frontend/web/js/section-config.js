/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(['underscore'], function (_) {
    'use strict';

    var baseUrls, sections, clientSideSections;

    var canonize = function(url){
        var route = url;
        for (var key in baseUrls) {
            route = url.replace(baseUrls[key], '');
            if (route != url) {
                break;
            }
        }
        return route.replace(/^\/?index.php\/?/, '').toLowerCase();
    };

    return {
        getAffectedSections: function (url) {
            var route = canonize(url);
            var actions = _.find(sections, function(val, section) {
                if (section.indexOf('*') >= 0) {
                    section = section.replace(/\*/g, '[^/]+') + '$';
                    var matched = route.match(section);
                    return matched && matched[0] == route;
                }
                return (route.indexOf(section) === 0);
            });

            return _.union(_.toArray(actions), _.toArray(sections['*']));
        },

        filterClientSideSections: function (sections) {
            if (Array.isArray(sections)) {
                return _.difference(sections, clientSideSections);
            }
            return sections;
        },

        isClientSideSection: function (sectionName) {
            return _.contains(clientSideSections, sectionName);
        },

        'Magento_Customer/js/section-config': function(options) {
            baseUrls = options.baseUrls;
            sections = options.sections;
            clientSideSections = options.clientSideSections;
        }
    };
});
