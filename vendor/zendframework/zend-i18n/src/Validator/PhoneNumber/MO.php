<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '853',
    'patterns' => array(
        'national' => array(
            'general' => '/^[268]\\d{7}$/',
            'fixed' => '/^(?:28[2-57-9]|8[2-57-9]\\d)\\d{5}$/',
            'mobile' => '/^6[2356]\\d{6}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
