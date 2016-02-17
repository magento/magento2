<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '253',
    'patterns' => array(
        'national' => array(
            'general' => '/^[27]\\d{7}$/',
            'fixed' => '/^2(?:1[2-5]|7[45])\\d{5}$/',
            'mobile' => '/^77[6-8]\\d{5}$/',
            'emergency' => '/^1[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
