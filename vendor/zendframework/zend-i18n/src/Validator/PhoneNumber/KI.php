<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '686',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-689]\\d{4}$/',
            'fixed' => '/^(?:[234]\\d|50|8[1-5])\\d{3}$/',
            'mobile' => '/^6\\d{4}|9(?:[0-8]\\d|9[015-8])\\d{2}$/',
            'shortcode' => '/^10(?:[0-8]|5[01259])$/',
            'emergency' => '/^99[2349]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
