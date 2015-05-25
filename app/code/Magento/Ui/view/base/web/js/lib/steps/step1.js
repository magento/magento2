/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery"
], function ($) {
    "use strict";

    var content = 'content1';

    return {
        title: 'step1',
        render: function(el) {
            el.append(content);
        },
        force: function(wizard) {
            console.log(this.title + ':force');
        },
        back: function(wizard) {
            console.log(this.title + ':back');
        },
        "Magento_Ui/js/lib/steps/step1": function (config, el) {
            content = config.content;
        }
    };
});
