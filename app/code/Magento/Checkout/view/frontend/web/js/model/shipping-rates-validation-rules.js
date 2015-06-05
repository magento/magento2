/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    ['jquery'],
    function ($) {
        "use strict";
        var ratesRules = {};
        return {
            registerRules: function(carrier, rules) {
                ratesRules[carrier] = rules.getRules();
            },
            getRules: function() {
                return ratesRules;
            },
            getObservableFields: function() {
                var self = this;
                var observableFields = [];
                $.each(self.getRules(), function(carrier, fields) {
                    $.each(fields, function(field, rules) {
                        if (rules.required && $.inArray(field, observableFields) == -1) {
                            observableFields.push(field);
                        }
                    });
                });
                return observableFields;
            }
        };
    }
);
