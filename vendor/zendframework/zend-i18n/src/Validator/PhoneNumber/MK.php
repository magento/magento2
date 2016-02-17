<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '389',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-578]\\d{7}$/',
            'fixed' => '/^(?:2(?:[23]\\d|5[124578]|6[01])|3(?:1[3-6]|[23][2-6]|4[2356])|4(?:[23][2-6]|4[3-6]|5[256]|6[25-8]|7[24-6]|8[4-6]))\\d{5}$/',
            'mobile' => '/^7(?:[0-25-8]\\d|33)\\d{5}$/',
            'tollfree' => '/^800\\d{5}$/',
            'premium' => '/^5[02-9]\\d{6}$/',
            'shared' => '/^8(?:0[1-9]|[1-9]\\d)\\d{5}$/',
            'emergency' => '/^1(?:12|9[234])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'fixed' => '/^\\d{6,8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
