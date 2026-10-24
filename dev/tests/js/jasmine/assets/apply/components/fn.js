/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
