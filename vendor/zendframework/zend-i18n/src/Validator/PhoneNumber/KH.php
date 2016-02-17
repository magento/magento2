<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '855',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-9]\\d{7,9}$/',
            'fixed' => '/^(?:2[3-6]|3[2-6]|4[2-4]|[567][2-5])(?:[2-47-9]|5\\d|6\\d?)\\d{5}$/',
            'mobile' => '/^(?:(?:1\\d|6[06-9]|7(?:[07-9]|6\\d))[1-9]|8(?:0[89]|[134679]\\d|5[2-689]|8\\d{2})|9(?:[0-589][1-9]|[67][1-9]\\d?))\\d{5}$/',
            'tollfree' => '/^1800(?:1\\d|2[019])\\d{4}$/',
            'premium' => '/^1900(?:1\\d|2[09])\\d{4}$/',
            'emergency' => '/^11[789]|666$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{10}$/',
            'premium' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
