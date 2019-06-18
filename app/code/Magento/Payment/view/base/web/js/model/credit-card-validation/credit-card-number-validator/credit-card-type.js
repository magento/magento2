/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'jquery',
    'mageUtils'
], function ($, utils) {
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
            pattern: '^(?:5[1-5][0-9]{2}|222[1-9]|22[3-9][0-9]|2[3-6][0-9]{2}|27[01][0-9]|2720)[0-9]{12}$',
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
            pattern: '^(3(0[0-5]|095|6|[8-9]))\\d*$',
            gaps: [4, 10],
            lengths: [14, 16, 17, 18, 19],
            code: {
                name: 'CVV',
                size: 3
            }
        },
        {
            title: 'Discover',
            type: 'DI',
            pattern: '^(6011(0|[2-4]|74|7[7-9]|8[6-9]|9)|6(4[4-9]|5))\\d*$',
            gaps: [4, 8, 12],
            lengths: [16, 17, 18, 19],
            code: {
                name: 'CID',
                size: 3
            }
        },
        {
            title: 'JCB',
            type: 'JCB',
            pattern: '^35(2[8-9]|[3-8])\\d*$',
            gaps: [4, 8, 12],
            lengths: [16, 17, 18, 19],
            code: {
                name: 'CVV',
                size: 3
            }
        },
        {
            title: 'UnionPay',
            type: 'UN',
            pattern: '^(622(1(2[6-9]|[3-9])|[3-8]|9([[0-1]|2[0-5]))|62[4-6]|628([2-8]))\\d*?$',
            gaps: [4, 8, 12],
            lengths: [16, 17, 18, 19],
            code: {
                name: 'CVN',
                size: 3
            }
        },
        {
            title: 'Maestro International',
            type: 'MI',
            pattern: '^(5(0|[6-9])|63|67(?!59|6770|6774))\\d*$',
            gaps: [4, 8, 12],
            lengths: [12, 13, 14, 15, 16, 17, 18, 19],
            code: {
                name: 'CVC',
                size: 3
            }
        },
        {
            title: 'Maestro Domestic',
            type: 'MD',
            pattern: '^6759(?!24|38|40|6[3-9]|70|76)|676770|676774\\d*$',
            gaps: [4, 8, 12],
            lengths: [12, 13, 14, 15, 16, 17, 18, 19],
            code: {
                name: 'CVC',
                size: 3
            }
        },
        {
            title: 'Hipercard',
            type: 'HC',
            pattern: '^((606282)|(637095)|(637568)|(637599)|(637609)|(637612))\\d*$',
            gaps: [4, 8, 12],
            lengths: [13, 16],
            code: {
                name: 'CVC',
                size: 3
            }
        },
        {
            title: 'Elo',
            type: 'ELO',
            pattern: '^((509091)|(636368)|(636297)|(504175)|(438935)|(40117[8-9])|(45763[1-2])|' +
                '(457393)|(431274)|(50990[0-2])|(5099[7-9][0-9])|(50996[4-9])|(509[1-8][0-9][0-9])|' +
                '(5090(0[0-2]|0[4-9]|1[2-9]|[24589][0-9]|3[1-9]|6[0-46-9]|7[0-24-9]))|' +
                '(5067(0[0-24-8]|1[0-24-9]|2[014-9]|3[0-379]|4[0-9]|5[0-3]|6[0-5]|7[0-8]))|' +
                '(6504(0[5-9]|1[0-9]|2[0-9]|3[0-9]))|' +
                '(6504(8[5-9]|9[0-9])|6505(0[0-9]|1[0-9]|2[0-9]|3[0-8]))|' +
                '(6505(4[1-9]|5[0-9]|6[0-9]|7[0-9]|8[0-9]|9[0-8]))|' +
                '(6507(0[0-9]|1[0-8]))|(65072[0-7])|(6509(0[1-9]|1[0-9]|20))|' +
                '(6516(5[2-9]|6[0-9]|7[0-9]))|(6550(0[0-9]|1[0-9]))|' +
                '(6550(2[1-9]|3[0-9]|4[0-9]|5[0-8])))\\d*$',
            gaps: [4, 8, 12],
            lengths: [16],
            code: {
                name: 'CVC',
                size: 3
            }
        },
        {
            title: 'Aura',
            type: 'AU',
            pattern: '^5078\\d*$',
            gaps: [4, 8, 12],
            lengths: [19],
            code: {
                name: 'CVC',
                size: 3
            }
        }
    ];

    return {
        /**
         * @param {*} cardNumber
         * @return {Array}
         */
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
    };
});
