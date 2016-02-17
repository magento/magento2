<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '681',
    'patterns' => array(
        'national' => array(
            'general' => '/^[5-7]\\d{5}$/',
            'fixed' => '/^(?:50|68|72)\\d{4}$/',
            'mobile' => '/^(?:50|68|72)\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
