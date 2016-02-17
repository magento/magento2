<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '378',
    'patterns' => array(
        'national' => array(
            'general' => '/^[05-7]\\d{7,9}$/',
            'fixed' => '/^0549(?:8[0157-9]|9\\d)\\d{4}$/',
            'mobile' => '/^6[16]\\d{6}$/',
            'premium' => '/^7[178]\\d{6}$/',
            'voip' => '/^5[158]\\d{6}$/',
            'emergency' => '/^11[358]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'mobile' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
