/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
(function () {
    'use strict';

    var Ap = Array.prototype,
        slice = Ap.slice,
        Fp = Function.prototype;

    if (!Fp.bind) {
        /**
         * PhantomJS doesn't support Function.prototype.bind natively, so
         * polyfill it whenever this module is required.
         *
         * @param  {*} context
         * @return {Function}
         */
        Fp.bind = function (context) {
            var func = this,
                args = slice.call(arguments, 1);
                
            function bound() {
                var invokedAsConstructor = func.prototype && (this instanceof func);

                return func.apply(
                    // Ignore the context parameter when invoking the bound function
                    // as a constructor. Note that this includes not only constructor
                    // invocations using the new keyword but also calls to base class
                    // constructors such as BaseClass.call(this, ...) or super(...).
                    !invokedAsConstructor && context || this,
                    args.concat(slice.call(arguments))
                );
            }

            // The bound function must share the .prototype of the unbound
            // function so that any object created by one constructor will count
            // as an instance of both constructors.
            bound.prototype = func.prototype;

            return bound;
        };
    }

})();
