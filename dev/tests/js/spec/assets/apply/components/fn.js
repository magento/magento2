define([], function () {
    'use strict';

    function fn() {
        fn.testCallback.apply(fn, arguments);
    }

    fn.testCallback = function () {};

    return fn;
});
