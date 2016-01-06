/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function () {
    'use strict';

    define([
        'Magento_Ui/js/modal/alert'
    ], function () {

        return function ($target, $owner, data) {
            $target.find(data.enableInContext + ' option[value="0"]').prop('selected', true);
            $target.find('label[for="' + $target.find(data.enableInContext).attr('id') + '"]').removeClass('enabled');
        };
    });
})();
