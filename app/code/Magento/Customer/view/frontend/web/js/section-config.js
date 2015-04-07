/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    var baseUrls, sections;

    return {
        getAffectedSections: function (url) {
            var route = url;
            for (var key in baseUrls) {
                var route = url.replace(baseUrls[key], '');
                if (route != url) {
                    break;
                }
            }

            route = route.replace(/^\/?index.php\/?/) + '/';
            for (var key in sections) {
                if (route.indexOf(key + '/') === 0) {
                    return sections[key];
                }
            }
        },
        'Magento_Customer/js/section-config': function(options) {
            baseUrls = options.baseUrls;
            sections = options.sections;
        }
    };
});
