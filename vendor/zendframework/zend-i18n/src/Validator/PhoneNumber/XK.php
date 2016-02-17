<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '383',
    'patterns' => array(
        'national' => array(
            'general' => '/^[126-9]\\d{4,11}|3(?:[0-79]\\d{3,10}|8[2-9]\\d{2,9})$/',
            'fixed' => '/^(?:1(?:[02-9][2-9]|1[1-9])\\d|2(?:[0-24-7][2-9]\\d|[389](?:0[2-9]|[2-9]\\d))|3(?:[0-8][2-9]\\d|9(?:[2-9]\\d|0[2-9])))\\d{3,8}$/',
            'mobile' => '/^6(?:[0-689]|7\\d)\\d{6,7}$/',
            'tollfree' => '/^800\\d{3,9}$/',
            'premium' => '/^(?:90[0169]|78\\d)\\d{3,7}$/',
            'uan' => '/^7[06]\\d{4,10}$/',
            'shortcode' => '/^1(?:1(?:[013-9]|\\d(2,4))|[89]\\d{1,4})$/',
            'emergency' => '/^112|9[234]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,12}$/',
            'fixed' => '/^\\d{5,12}$/',
            'mobile' => '/^\\d{8,10}$/',
            'tollfree' => '/^\\d{6,12}$/',
            'premium' => '/^\\d{6,12}$/',
            'uan' => '/^\\d{6,12}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
