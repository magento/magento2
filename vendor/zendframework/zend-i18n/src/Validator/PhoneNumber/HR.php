<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '385',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-7]\\d{5,8}|[89]\\d{6,11}$/',
            'fixed' => '/^1\\d{7}|(?:2[0-3]|3[1-5]|4[02-47-9]|5[1-3])\\d{6}$/',
            'uan' => '/^62\\d{6,7}$/',
            'mobile' => '/^9[1257-9]\\d{6,10}$/',
            'tollfree' => '/^80[01]\\d{4,7}$/',
            'premium' => '/^6(?:[09]\\d{7}|[145]\\d{4,7})$/',
            'personal' => '/^7[45]\\d{4,7}$/',
            'emergency' => '/^1(?:12|92)|9[34]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,12}$/',
            'fixed' => '/^\\d{6,8}$/',
            'uan' => '/^\\d{8,9}$/',
            'mobile' => '/^\\d{8,12}$/',
            'tollfree' => '/^\\d{7,10}$/',
            'premium' => '/^\\d{6,9}$/',
            'personal' => '/^\\d{6,9}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
