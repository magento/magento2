<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '20',
    'patterns' => array(
        'national' => array(
            'general' => '/^1\\d{4,9}|[2456]\\d{8}|3\\d{7}|[89]\\d{8,9}$/',
            'fixed' => '/^(?:1(3[23]\\d|5[23])|2[2-4]\\d{2}|3\\d{2}|4(?:0[2-5]|[578][23]|64)\\d|5(?:0[2-7]|[57][23])\\d|6[24-689]3\\d|8(?:2[2-57]|4[26]|6[237]|8[2-4])\\d|9(?:2[27]|3[24]|52|6[2356]|7[2-4])\\d)\\d{5}|1[69]\\d{3}$/',
            'mobile' => '/^1(?:0[01269]|1[1245]|2[0-278])\\d{7}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^900\\d{7}$/',
            'emergency' => '/^1(?:2[23]|80)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,10}$/',
            'fixed' => '/^\\d{5,9}$/',
            'mobile' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
