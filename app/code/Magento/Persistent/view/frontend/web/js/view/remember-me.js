/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        "ko",
        "uiComponent"
    ],
    function (ko, Component) {
        "use strict";
        var persistenceConfig = window.checkoutConfig.persistenceConfig;
        return Component.extend({
            defaults: {
                template: "Magento_Persistent/remember-me"
            },
            dataScope: "global",
            isRememberMeCheckboxVisible: ko.observable(persistenceConfig.isRememberMeCheckboxVisible),
            isRememberMeCheckboxChecked: ko.observable(persistenceConfig.isRememberMeCheckboxChecked)
        });
    }
);
