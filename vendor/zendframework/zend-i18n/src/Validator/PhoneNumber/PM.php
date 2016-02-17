<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '508',
    'patterns' => array(
        'national' => array(
            'general' => '/^[45]\\d{5}$/',
            'fixed' => '/^41\\d{4}$/',
            'mobile' => '/^55\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
