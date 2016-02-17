<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '359',
    'patterns' => array(
        'national' => array(
            'general' => '/^[23567]\\d{5,7}|[489]\\d{6,8}$/',
            'fixed' => '/^2(?:[0-8]\\d{5,6}|9\\d{4,6})|(?:[36]\\d|5[1-9]|8[1-6]|9[1-7])\\d{5,6}|(?:4(?:[124-7]\\d|3[1-6])|7(?:0[1-9]|[1-9]\\d))\\d{4,5}$/',
            'mobile' => '/^(?:8[7-9]|98)\\d{7}|4(?:3[0789]|8\\d)\\d{5}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^90\\d{6}$/',
            'personal' => '/^700\\d{5}$/',
            'emergency' => '/^1(?:12|50|6[06])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,9}$/',
            'fixed' => '/^\\d{5,8}$/',
            'mobile' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
