try {
    if (!window.localStorage || !window.sessionStorage) {
        throw new Error();
    }

    localStorage.setItem('storage_test', 1);
    localStorage.removeItem('storage_test');
} catch (e) {
    (function () {
        'use strict';

        /**
         * Returns a storage object to shim local or sessionStorage
         * @param {String} type - either 'local' or 'session'
         */
        var Storage = function (type) {
            var data;

            /**
             * Creates a cookie
             * @param {String} name
             * @param {String} value
             * @param {Integer} days
             */
            function createCookie(name, value, days) {
                var date, expires;

                if (days) {
                    date = new Date();
                    date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
                    expires = '; expires=' + date.toGMTString();
                } else {
                    expires = '';
                }
                document.cookie = name + '=' + value + expires + '; path=/';
            }

            /**
             * Reads value of a cookie
             * @param {String} name
             */
            function readCookie(name) {
                var nameEQ = name + '=',
                    ca = document.cookie.split(';'),
                    i = 0,
                    c;

                for (i = 0; i < ca.length; i++) {
                    c = ca[i];

                    while (c.charAt(0) === ' ') {
                        c = c.substring(1, c.length);
                    }

                    if (c.indexOf(nameEQ) === 0) {
                        return c.substring(nameEQ.length, c.length);
                    }
                }

                return null;
            }

            /**
             * Returns cookie name based upon the storage type.
             * If this is session storage, the function returns a unique cookie per tab
             */
            function getCookieName() {

                if (type !== 'session') {
                    return 'localstorage';
                }

                if (!window.name) {
                    window.name = new Date().getTime();
                }

                return 'sessionStorage' + window.name;
            }

            /**
             * Sets storage cookie to a data object
             * @param {Object} dataObject
             */
            function setData(dataObject) {
                data = encodeURIComponent(JSON.stringify(dataObject));
                createCookie(getCookieName(), data, 365);
            }

            /**
             * Clears value of cookie data
             */
            function clearData() {
                createCookie(getCookieName(), '', 365);
            }

            /**
             * @returns value of cookie data
             */
            function getData() {
                var dataResponse = readCookie(getCookieName());

                return dataResponse ? JSON.parse(decodeURIComponent(dataResponse)) : {};
            }

            data = getData();

            return {
                length: 0,

                /**
                 * Clears data from storage
                 */
                clear: function () {
                    data = {};
                    this.length = 0;
                    clearData();
                },

                /**
                 * Gets an item from storage
                 * @param {String} key
                 */
                getItem: function (key) {
                    return data[key] === undefined ? null : data[key];
                },

                /**
                 * Gets an item by index from storage
                 * @param {Integer} i
                 */
                key: function (i) {
                    var ctr = 0,
                        k;

                    for (k in data) {

                        if (data.hasOwnProperty(k)) {

                            // eslint-disable-next-line max-depth
                            if (ctr.toString() === i.toString()) {
                                return k;
                            }
                            ctr++;
                        }
                    }

                    return null;
                },

                /**
                 * Removes an item from storage
                 * @param {String} key
                 */
                removeItem: function (key) {
                    delete data[key];
                    this.length--;
                    setData(data);
                },

                /**
                 * Sets an item from storage
                 * @param {String} key
                 * @param {String} value
                 */
                setItem: function (key, value) {
                    data[key] = value.toString();
                    this.length++;
                    setData(data);
                }
            };
        };

        window.localStorage.prototype = window.localStorage = new Storage('local');
        window.sessionStorage.prototype = window.sessionStorage = new Storage('session');
    })();
}
