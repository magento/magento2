<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '674',
    'patterns' => array(
        'national' => array(
            'general' => '/^[458]\\d{6}$/',
            'fixed' => '/^(?:444|888)\\d{4}$/',
            'mobile' => '/^55[5-9]\\d{4}$/',
            'shortcode' => '/^1(?:23|92)$/',
            'emergency' => '/^11[0-2]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
