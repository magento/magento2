<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '372',
    'patterns' => array(
        'national' => array(
            'general' => '/^1\\d{3,4}|[3-9]\\d{6,7}|800\\d{6,7}$/',
            'fixed' => '/^(?:3[23589]|4(?:0\\d|[3-8])|6\\d|7[1-9]|88)\\d{5}$/',
            'mobile' => '/^(?:5\\d|8[1-5])\\d{6}|5(?:[02]\\d{2}|1(?:[0-8]\\d|95)|5[0-478]\\d|64[0-4]|65[1-589])\\d{3}$/',
            'tollfree' => '/^800(?:0\\d{3}|1\\d|[2-9])\\d{3}$/',
            'premium' => '/^900\\d{4}$/',
            'personal' => '/^70[0-2]\\d{5}$/',
            'uan' => '/^1(?:2[01245]|3[0-6]|4[1-489]|5[0-59]|6[1-46-9]|7[0-27-9]|8[189]|9[012])\\d{1,2}$/',
            'shortcode' => '/^1(?:1[13-9]|[2-9]\\d)$/',
            'emergency' => '/^11[02]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4,10}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{7,8}$/',
            'tollfree' => '/^\\d{7,10}$/',
            'premium' => '/^\\d{7}$/',
            'personal' => '/^\\d{8}$/',
            'uan' => '/^\\d{4,5}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
