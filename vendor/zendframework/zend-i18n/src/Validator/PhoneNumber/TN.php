<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '216',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-57-9]\\d{7}$/',
            'fixed' => '/^(?:3[012]|7\\d)\\d{6}$/',
            'mobile' => '/^(?:[259]\\d|4[0-2])\\d{6}$/',
            'premium' => '/^8[0128]\\d{6}$/',
            'emergency' => '/^19[078]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
