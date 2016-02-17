<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '290',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{3}$/',
            'fixed' => '/^(?:[2-468]\\d|7[01])\\d{2}$/',
            'premium' => '/^(?:[59]\\d|7[2-9])\\d{2}$/',
            'shortcode' => '/^1\\d{2,3}$/',
            'emergency' => '/^9(?:11|99)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
