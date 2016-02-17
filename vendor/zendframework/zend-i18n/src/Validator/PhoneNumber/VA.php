<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '379',
    'patterns' => array(
        'national' => array(
            'general' => '/^06\\d{8}$/',
            'fixed' => '/^06698\\d{5}$/',
            'mobile' => '/^N/A$/',
            'emergency' => '/^11[2358]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{10}$/',
            'mobile' => '/^N/A$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
