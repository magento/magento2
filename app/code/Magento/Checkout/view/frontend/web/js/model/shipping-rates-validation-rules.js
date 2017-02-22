/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['jquery'],
    function ($) {
        "use strict";
        var ratesRules = {};
        var checkoutConfig = window.checkoutConfig;
        return {
            registerRules: function(carrier, rules) {
                if (checkoutConfig.activeCarriers.indexOf(carrier) != -1) {
                    ratesRules[carrier] = rules.getRules();
                }
            },
            getRules: function() {
                return ratesRules;
            },
            getObservableFields: function() {
                var self = this;
                var observableFields = [];
                $.each(self.getRules(), function(carrier, fields) {
                    $.each(fields, function(field, rules) {
                        if (observableFields.indexOf(field) == -1) {
                            observableFields.push(field);
                        }
                    });
                });
                return observableFields;
            }
        };
    }
);
