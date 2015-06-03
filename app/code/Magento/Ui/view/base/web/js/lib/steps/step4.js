/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery"
], function ($) {
    "use strict";

    var content = 'content4';

    return {
        title: 'step4',
        render: function(el) {
        },
        force: function(wizard) {
            console.log(this.title + ':force');
        },
        back: function(wizard) {
            console.log(this.title + ':back');
        },
        "Magento_Ui/js/lib/steps/step4": function (config, el) {
            content = config.content;
        }
    };
});
