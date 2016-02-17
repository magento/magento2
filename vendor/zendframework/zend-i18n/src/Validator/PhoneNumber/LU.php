<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '352',
    'patterns' => array(
        'national' => array(
            'general' => '/^[24-9]\\d{3,10}|3(?:[0-46-9]\\d{2,9}|5[013-9]\\d{1,8})$/',
            'fixed' => '/^(?:2(?:2\\d{1,2}|3[2-9]|[67]\\d|4[1-8]\\d?|5[1-5]\\d?|9[0-24-9]\\d?)|3(?:[059][05-9]|[13]\\d|[26][015-9]|4[0-26-9]|7[0-389]|8[08])\\d?|4\\d{2,3}|5(?:[01458]\\d|[27][0-69]|3[0-3]|[69][0-7])\\d?|7(?:1[019]|2[05-9]|3[05]|[45][07-9]|[679][089]|8[06-9])\\d?|8(?:0[2-9]|1[0-36-9]|3[3-9]|[469]9|[58][7-9]|7[89])\\d?|9(?:0[89]|2[0-49]|37|49|5[0-27-9]|7[7-9]|9[0-478])\\d?)\\d{1,7}$/',
            'mobile' => '/^6[269][18]\\d{6}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^90[01]\\d{5}$/',
            'shared' => '/^801\\d{5}$/',
            'personal' => '/^70\\d{6}$/',
            'voip' => '/^20\\d{2,8}$/',
            'shortcode' => '/^12\\d{3}$/',
            'emergency' => '/^11[23]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4,11}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'personal' => '/^\\d{8}$/',
            'voip' => '/^\\d{4,10}$/',
            'shortcode' => '/^\\d{3,5}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
