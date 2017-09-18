/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'mageUtils',
    'moment'
], function (utils, moment) {
    'use strict';

    describe('mageUtils', function () {

        it('Check convertToMomentFormat function', function () {
            var format, momentFormat;

            format = 'M/d/yy';
            momentFormat = 'M/DD/YYYY';
            expect(utils.convertToMomentFormat(format)).toBe(momentFormat);
        });

        it('Check "filterFormData" method', function () {
            var suffix = 'prepared-for-send',
                separator = '-',
                data = {
                    key: 'value-prepared-before-save'
                };

            expect(utils.filterFormData(data, suffix, separator)).toEqual(data);
            expect(utils.filterFormData(data, suffix)).toEqual(data);
            expect(utils.filterFormData(data)).toEqual(data);
            expect(utils.filterFormData()).toEqual({});
        });

        it('Check convertToMomentFormat function for all Magento supported locales', function () {

            var fixture,
            localeValues,
            format,
            expectedValue,
            momentFormat,
            dt,
            m,
            p;

            fixture = {
                'af_ZA': {
                    'locale': 'af_ZA',
                    'localeInfo': {
                        'format': 'y-MM-dd',
                        'expectedValue': '2016-11-17'
                    }
                },
                'az_Latn_AZ': {
                    'locale': 'az_Latn_AZ',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'id_ID': {
                    'locale': 'id_ID',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'ms_Latn_MY': {
                    'locale': 'ms_Latn_MY',
                    'localeInfo': {
                        'format': 'd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'bs_Latn_BA': {
                    'locale': 'bs_Latn_BA',
                    'localeInfo': {
                        'format': 'dd.MM.yy.',
                        'expectedValue': '17.11.2016.'
                    }
                },
                'ca_ES': {
                    'locale': 'ca_ES',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'cy_GB': {
                    'locale': 'cy_GB',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'da_DK': {
                    'locale': 'da_DK',
                    'localeInfo': {
                        'format': 'dd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'de_DE': {
                    'locale': 'de_DE',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'de_CH': {
                    'locale': 'de_CH',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'de_AT': {
                    'locale': 'de_AT',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'et_EE': {
                    'locale': 'et_EE',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'en_AU': {
                    'locale': 'en_AU',
                    'localeInfo': {
                        'format': 'd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'en_CA': {
                    'locale': 'en_CA',
                    'localeInfo': {
                        'format': 'y-MM-dd',
                        'expectedValue': '2016-11-17'
                    }
                },
                'en_IE': {
                    'locale': 'en_IE',
                    'localeInfo': {
                        'format': 'dd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'en_NZ': {
                    'locale': 'en_NZ',
                    'localeInfo': {
                        'format': 'd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'en_GB': {
                    'locale': 'en_GB',
                    'localeInfo': {
                        'format': 'dd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'en_US': {
                    'locale': 'en_US',
                    'localeInfo': {
                        'format': 'M/d/yy',
                        'expectedValue': '11/17/2016'
                    }
                },
                'es_AR': {
                    'locale': 'es_AR',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'es_CL': {
                    'locale': 'es_CL',
                    'localeInfo': {
                        'format': 'dd-MM-yy',
                        'expectedValue': '17-11-2016'
                    }
                },
                'es_CO': {
                    'locale': 'es_CO',
                    'localeInfo': {
                        'format': 'd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'es_CR': {
                    'locale': 'es_CR',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'es_ES': {
                    'locale': 'es_ES',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'es_MX': {
                    'locale': 'es_MX',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'es_PA': {
                    'locale': 'es_PA',
                    'localeInfo': {
                        'format': 'MM/dd/yy',
                        'expectedValue': '11/17/2016'
                    }
                },
                'es_PE': {
                    'locale': 'es_PE',
                    'localeInfo': {
                        'format': 'd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'es_VE': {
                    'locale': 'es_VE',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'eu_ES': {
                    'locale': 'eu_ES',
                    'localeInfo': {
                        'format': 'y/MM/dd',
                        'expectedValue': '2016/11/17'
                    }
                },
                'fil_PH': {
                    'locale': 'fil_PH',
                    'localeInfo': {
                        'format': 'M/d/yy',
                        'expectedValue': '11/17/2016'
                    }
                },
                'fr_BE': {
                    'locale': 'fr_BE',
                    'localeInfo': {
                        'format': 'd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'fr_CA': {
                    'locale': 'fr_CA',
                    'localeInfo': {
                        'format': 'yy-MM-dd',
                        'expectedValue': '2016-11-17'
                    }
                },
                'fr_FR': {
                    'locale': 'fr_FR',
                    'localeInfo': {
                        'format': 'dd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'gl_ES': {
                    'locale': 'gl_ES',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'hr_HR': {
                    'locale': 'hr_HR',
                    'localeInfo': {
                        'format': 'dd.MM.y.',
                        'expectedValue': '17.11.2016.'
                    }
                },
                'it_IT': {
                    'locale': 'it_IT',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'it_CH': {
                    'locale': 'it_CH',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'sw_KE': {
                    'locale': 'sw_KE',
                    'localeInfo': {
                        'format': 'dd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'lv_LV': {
                    'locale': 'lv_LV',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'lt_LT': {
                    'locale': 'lt_LT',
                    'localeInfo': {
                        'format': 'y-MM-dd',
                        'expectedValue': '2016-11-17'
                    }
                },
                'hu_HU': {
                    'locale': 'hu_HU',
                    'localeInfo': {
                        'format': 'y. MM. dd.',
                        'expectedValue': '2016. 11. 17.'
                    }
                },
                'nl_BE': {
                    'locale': 'nl_BE',
                    'localeInfo': {
                        'format': 'd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'nl_NL': {
                    'locale': 'nl_NL',
                    'localeInfo': {
                        'format': 'dd-MM-yy',
                        'expectedValue': '17-11-2016'
                    }
                },
                'nb_NO': {
                    'locale': 'nb_NO',
                    'localeInfo': {
                        'format': 'dd.MM.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'nn_NO': {
                    'locale': 'nn_NO',
                    'localeInfo': {
                        'format': 'dd.MM.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'pl_PL': {
                    'locale': 'pl_PL',
                    'localeInfo': {
                        'format': 'dd.MM.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'pt_BR': {
                    'locale': 'pt_BR',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'pt_PT': {
                    'locale': 'pt_PT',
                    'localeInfo': {
                        'format': 'dd/MM/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'ro_RO': {
                    'locale': 'ro_RO',
                    'localeInfo': {
                        'format': 'dd.MM.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'sq_AL': {
                    'locale': 'sq_AL',
                    'localeInfo': {
                        'format': 'd.M.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'sk_SK': {
                    'locale': 'sk_SK',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'sl_SI': {
                    'locale': 'sl_SI',
                    'localeInfo': {
                        'format': 'd. MM. yy',
                        'expectedValue': '17. 11. 2016'
                    }
                },
                'fi_FI': {
                    'locale': 'fi_FI',
                    'localeInfo': {
                        'format': 'd.M.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'sv_SE': {
                    'locale': 'sv_SE',
                    'localeInfo': {
                        'format': 'y-MM-dd',
                        'expectedValue': '2016-11-17'
                    }
                },
                'vi_VN': {
                    'locale': 'vi_VN',
                    'localeInfo': {
                        'format': 'dd/MM/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'tr_TR': {
                    'locale': 'tr_TR',
                    'localeInfo': {
                        'format': 'd.MM.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'is_IS': {
                    'locale': 'is_IS',
                    'localeInfo': {
                        'format': 'd.M.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'cs_CZ': {
                    'locale': 'cs_CZ',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'el_GR': {
                    'locale': 'el_GR',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'be_BY': {
                    'locale': 'be_BY',
                    'localeInfo': {
                        'format': 'd.M.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'bg_BG': {
                    'locale': 'bg_BG',
                    'localeInfo': {
                        'format': 'd.MM.yy г.',
                        'expectedValue': '17.11.2016 г.'
                    }
                },
                'mk_MK': {
                    'locale': 'mk_MK',
                    'localeInfo': {
                        'format': 'dd.M.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'mn_Cyrl_MN': {
                    'locale': 'mn_Cyrl_MN',
                    'localeInfo': {
                        'format': 'y-MM-dd',
                        'expectedValue': '2016-11-17'
                    }
                },
                'ru_RU': {
                    'locale': 'ru_RU',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'sr_Cyrl_RS': {
                    'locale': 'sr_Cyrl_RS',
                    'localeInfo': {
                        'format': 'd.M.yy.',
                        'expectedValue': '17.11.2016.'
                    }
                },
                'uk_UA': {
                    'locale': 'uk_UA',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'he_IL': {
                    'locale': 'he_IL',
                    'localeInfo': {
                        'format': 'd.M.y',
                        'expectedValue': '17.11.2016'
                    }
                },
                'ar_DZ': {
                    'locale': 'ar_DZ',
                    'localeInfo': {
                        'format': 'd‏/M‏/y',
                        'expectedValue': '17‏/11‏/2016'
                    }
                },
                'ar_KW': {
                    'locale': 'ar_KW',
                    'localeInfo': {
                        'format': 'd‏/M‏/y',
                        'expectedValue': '17‏/11‏/2016'
                    }
                },
                'ar_MA': {
                    'locale': 'ar_MA',
                    'localeInfo': {
                        'format': 'd‏/M‏/y',
                        'expectedValue': '17‏/11‏/2016'
                    }
                },
                'ar_SA': {
                    'locale': 'ar_SA',
                    'localeInfo': {
                        'format': 'd‏/M‏/y',
                        'expectedValue': '17‏/11‏/2016'
                    }
                },
                'ar_EG': {
                    'locale': 'ar_EG',
                    'localeInfo': {
                        'format': 'd‏/M‏/y',
                        'expectedValue':  '17‏/11‏/2016'
                    }
                },
                'fa_IR': {
                    'locale': 'fa_IR',
                    'localeInfo': {
                        'format': 'y/M/d G',
                        'expectedValue': '2016/11/17 G'
                    }
                },
                'hi_IN': {
                    'locale': 'hi_IN',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'bn_BD': {
                    'locale': 'bn_BD',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'gu_IN': {
                    'locale': 'gu_IN',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'th_TH': {
                    'locale': 'th_TH',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'lo_LA': {
                    'locale': 'lo_LA',
                    'localeInfo': {
                        'format': 'd/M/y',
                        'expectedValue': '17/11/2016'
                    }
                },
                'ka_GE': {
                    'locale': 'ka_GE',
                    'localeInfo': {
                        'format': 'dd.MM.yy',
                        'expectedValue': '17.11.2016'
                    }
                },
                'km_KH': {
                    'locale': 'km_KH',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'zh_Hans_CN': {
                    'locale': 'zh_Hans_CN',
                    'localeInfo': {
                        'format': 'yy/M/d',
                        'expectedValue': '2016/11/17'
                    }
                },
                'zh_Hant_HK': {
                    'locale': 'zh_Hant_HK',
                    'localeInfo': {
                        'format': 'd/M/yy',
                        'expectedValue': '17/11/2016'
                    }
                },
                'zh_Hant_TW': {
                    'locale': 'zh_Hant_TW',
                    'localeInfo': {
                        'format': 'y/M/d',
                        'expectedValue': '2016/11/17'
                    }
                },
                'ja_JP': {
                    'locale': 'ja_JP',
                    'localeInfo': {
                        'format': 'y/MM/dd',
                        'expectedValue': '2016/11/17'
                    }
                },
                'ko_KR': {
                    'locale': 'ko_KR',
                    'localeInfo': {
                        'format': 'yy. M. d.',
                        'expectedValue': '2016. 11. 17.'
                    }
                }
            };

            for (p in fixture) {
                if (fixture.hasOwnProperty(p)) {
                    localeValues = fixture[p];
                    format = localeValues.localeInfo.format;
                    expectedValue = localeValues.localeInfo.expectedValue;
                    momentFormat = utils.convertToMomentFormat(format);
                    dt = moment('2016-11-17');
                    m = moment(dt, momentFormat);

                    expect(m.format(momentFormat)).toBe(expectedValue);
                }
            }
        });
    });
});
