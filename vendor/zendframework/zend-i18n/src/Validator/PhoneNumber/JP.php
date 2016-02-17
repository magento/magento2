<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '81',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-9]\\d{8,9}|0(?:[36]\\d{7,14}|7\\d{5,7}|8\\d{7})$/',
            'fixed' => '/^(?:1(?:1[235-8]|2[3-6]|3[3-9]|4[2-6]|[58][2-8]|6[2-7]|7[2-9]|9[1-9])|2[2-9]\\d|[36][1-9]\\d|4(?:6[02-8]|[2-578]\\d|9[2-59])|5(?:6[1-9]|7[2-8]|[2-589]\\d)|7(?:3[4-9]|4[02-9]|[25-9]\\d)|8(?:3[2-9]|4[5-9]|5[1-9]|8[03-9]|[2679]\\d)|9(?:[679][1-9]|[2-58]\\d))\\d{6}$/',
            'mobile' => '/^(?:[79]0\\d|80[1-9])\\d{7}$/',
            'pager' => '/^20\\d{8}$/',
            'tollfree' => '/^120\\d{6}|800\\d{7}|0(?:37\\d{6,13}|66\\d{6,13}|777(?:[01]\\d{2}|5\\d{3}|8\\d{4})|882[1245]\\d{4})$/',
            'premium' => '/^990\\d{6}$/',
            'personal' => '/^60\\d{7}$/',
            'voip' => '/^50\\d{8}$/',
            'uan' => '/^570\\d{6}$/',
            'emergency' => '/^11[09]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,16}$/',
            'fixed' => '/^\\d{9}$/',
            'mobile' => '/^\\d{10}$/',
            'pager' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{7,16}$/',
            'premium' => '/^\\d{9}$/',
            'personal' => '/^\\d{9}$/',
            'voip' => '/^\\d{10}$/',
            'uan' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
