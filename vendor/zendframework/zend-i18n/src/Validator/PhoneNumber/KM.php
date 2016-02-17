<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '269',
    'patterns' => array(
        'national' => array(
            'general' => '/^[379]\\d{6}$/',
            'fixed' => '/^7(?:6[0-37-9]|7[0-57-9])\\d{4}$/',
            'mobile' => '/^3[234]\\d{5}$/',
            'premium' => '/^(?:39[01]|9[01]0)\\d{4}$/',
            'emergency' => '/^1[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
