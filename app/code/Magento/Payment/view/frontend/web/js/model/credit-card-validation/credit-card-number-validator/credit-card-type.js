/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'jquery',
        'mageUtils'
    ],
    function ($, utils) {
        'use strict';
        var types = [
            {
                title: 'Visa',
                type: 'VI',
                pattern: '^4\\d*$',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVV',
                    size: 3
                }
            },
            {
                title: 'MasterCard',
                type: 'MC',
                pattern: '^5([1-5]\\d*)?$',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVC',
                    size: 3
                }
            },
            {
                title: 'American Express',
                type: 'AE',
                pattern: '^3([47]\\d*)?$',
                isAmex: true,
                gaps: [4, 10],
                lengths: [15],
                code: {
                    name: 'CID',
                    size: 4
                }
            },
            {
                title: 'Diners',
                type: 'DN',
                pattern: '^3((0([0-5]\\d*)?)|[689]\\d*)?$',
                gaps: [4, 10],
                lengths: [14],
                code: {
                    name: 'CVV',
                    size: 3
                }
            },
            {
                title: 'Discover',
                type: 'DI',
                pattern: '^6(0|01|011\\d*|5\\d*|4|4[4-9]\\d*)?$',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CID',
                    size: 3
                }
            },
            {
                title: 'JCB',
                type: 'JC',
                pattern: '^((2|21|213|2131\\d*)|(1|18|180|1800\\d*)|(3|35\\d*))$',
                gaps: [4, 8, 12],
                lengths: [16],
                code: {
                    name: 'CVV',
                    size: 3
                }
            },
            {
                title: 'UnionPay',
                type: 'UN',
                pattern: '^6(2\\d*)?$',
                gaps: [4, 8, 12],
                lengths: [16, 17, 18, 19],
                code: {
                    name: 'CVN',
                    size: 3
                }
            },
            {
                title: 'Maestro',
                type: 'SM',
                pattern: '(^(5[0678])[0-9]{11,18}$)' +
                '|(^(6[^05])[0-9]{11,18}$)' +
                '|(^(601)[^1][0-9]{9,16}$)' +
                '|(^(6011)[0-9]{9,11}$)' +
                '|(^(6011)[0-9]{13,16}$)' +
                '|(^(65)[0-9]{11,13}$)' +
                '|(^(65)[0-9]{15,18}$)' +
                '|(^(49030)[2-9]([0-9]{10}$' +
                '|[0-9]{12,13}$))' +
                '|(^(49033)[5-9]([0-9]{10}$' +
                '|[0-9]{12,13}$))' +
                '|(^(49110)[1-2]([0-9]{10}$' +
                '|[0-9]{12,13}$))' +
                '|(^(49117)[4-9]([0-9]{10}$|[0-9]{12,13}$))' +
                '|(^(49118)[0-2]([0-9]{10}$|[0-9]{12,13}$))' +
                '|(^(4936)([0-9]{12}$|[0-9]{14,15}$))',
                gaps: [4, 8, 12],
                lengths: [12, 13, 14, 15, 16, 17, 18, 19],
                code: {
                    name: 'CVC',
                    size: 3
                }
            }
        ];
        return {
            getCardTypes: function (cardNumber) {
                var i, value,
                    result = [];

                if (utils.isEmpty(cardNumber)) {
                    return result;
                }

                if (cardNumber === '') {
                    return $.extend(true, {}, types);
                }

                for (i = 0; i < types.length; i++) {
                    value = types[i];

                    if (new RegExp(value.pattern).test(cardNumber)) {
                        result.push($.extend(true, {}, value));
                    }
                }
                return result;
            }
        }
    }
);
