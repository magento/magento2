/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */

define(
    [
        'ko',
        'uiComponent'
    ],
    function (ko, Component) {
        'use strict';

        let persistenceConfig = window.checkoutConfig.persistenceConfig;

        return Component.extend({
            defaults: {
                template: 'Magento_Persistent/remember-me'
            },
            isRememberMeCheckboxVisible: ko.observable(persistenceConfig.isRememberMeCheckboxVisible),
            isRememberMeCheckboxChecked: ko.observable(persistenceConfig.isRememberMeCheckboxChecked)
        });
    }
);
