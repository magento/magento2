<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '32',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-9]\\d{7,8}$/',
            'fixed' => '/^(?:1[0-69]|[23][2-8]|[49][23]|5\\d|6[013-57-9]|71)\\d{6}|8(?:0[1-9]|[1-79]\\d)\\d{5}$/',
            'mobile' => '/^4(?:[679]\\d|8[03-9])\\d{6}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^(?:90|7[07])\\d{6}$/',
            'uan' => '/^78\\d{6}$/',
            'emergency' => '/^1(?:0[01]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'uan' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
