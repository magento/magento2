<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '689',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{5}$/',
            'fixed' => '/^(?:4(?:[02-9]\\d|1[02-9])|[5689]\\d{2})\\d{3}$/',
            'mobile' => '/^(?:[27]\\d{2}|3[0-79]\\d|411)\\d{3}$/',
            'emergency' => '/^1[578]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
