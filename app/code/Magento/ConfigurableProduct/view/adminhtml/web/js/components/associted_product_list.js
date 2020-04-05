define([
    'uiComponent',
    'ko'
], function (Component, ko) {
    "use strict";
    return Component.extend({
        defaults: {
            isShowAddProductButton: ko.observable(false)
        }
    });
});