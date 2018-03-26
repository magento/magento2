(function () {
    'use strict';

    window.require = {
        baseUrl: JSON.parse(document.getElementById('legacyJS_require').textContent).require
    };
}());
