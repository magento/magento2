/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery"
], function ($) {
    "use strict";

    var content = 'content3';

    return {
        title: 'step3',
        render: function(el) {
        },
        force: function(wizard) {
            console.log(this.title + ':force');
        },
        back: function(wizard) {
            console.log(this.title + ':back');
        },
        "Magento_Ui/js/lib/steps/step3": function (config, el) {
            content = config.content;
        }
    };
});
