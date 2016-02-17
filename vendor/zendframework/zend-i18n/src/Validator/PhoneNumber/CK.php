<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '682',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-57]\\d{4}$/',
            'fixed' => '/^(?:2\\d|3[13-7]|4[1-5])\\d{3}$/',
            'mobile' => '/^(?:5[0-68]|7\\d)\\d{3}$/',
            'emergency' => '/^99[689]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
