<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '500',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-7]\\d{4}$/',
            'fixed' => '/^[2-47]\\d{4}$/',
            'mobile' => '/^[56]\\d{4}$/',
            'shortcode' => '/^1\\d{2}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
