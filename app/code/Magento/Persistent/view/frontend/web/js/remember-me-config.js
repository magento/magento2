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

        return Component.extend({
            dataScope: 'global',
            config: {},

            /** @inheritdoc */
            initialize: function () {
                this._super();

                window.rememberMeConfig = this.config;
            }
        });
    }
);
