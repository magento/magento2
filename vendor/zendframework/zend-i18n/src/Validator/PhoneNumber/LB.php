<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '961',
    'patterns' => array(
        'national' => array(
            'general' => '/^[13-9]\\d{6,7}$/',
            'fixed' => '/^(?:[14-6]\\d{2}|7(?:[2-579]\\d|62|8[0-7])|[89][2-9]\\d)\\d{4}$/',
            'mobile' => '/^(?:3\\d|7(?:[01]\\d|6[013-9]|8[89]|91))\\d{5}$/',
            'premium' => '/^9[01]\\d{6}$/',
            'shared' => '/^8[01]\\d{6}$/',
            'emergency' => '/^1(?:12|40|75)|999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7,8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
