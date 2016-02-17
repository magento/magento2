<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '226',
    'patterns' => array(
        'national' => array(
            'general' => '/^[24-7]\\d{7}$/',
            'fixed' => '/^(?:20(?:49|5[23]|9[016-9])|40(?:4[56]|5[4-6]|7[0179])|50[34]\\d)\\d{4}$/',
            'mobile' => '/^(?:6(?:[056]\\d|1[0-3]|8[0-2]|90)|7(?:[02-68]\\d|1[0-4689]|7[0-69]|9[0-689]))\\d{5}$/',
            'emergency' => '/^1[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
