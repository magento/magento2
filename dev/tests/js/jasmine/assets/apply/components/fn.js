/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([], function () {
    'use strict';

    /**
     * Function used to be a placeholder for mage-init directive.
     */
    function fn() {
        fn.testCallback.apply(fn, arguments);
    }

    /**
     * Function whose call wll be tested.
     */
    fn.testCallback = function () {};

    return fn;
});
