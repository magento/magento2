<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '229',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2689]\\d{7}|7\\d{3}$/',
            'fixed' => '/^2(?:02|1[037]|2[45]|3[68])\\d{5}$/',
            'mobile' => '/^(?:6[46]|9[03-8])\\d{6}$/',
            'tollfree' => '/^7[3-5]\\d{2}$/',
            'voip' => '/^857[58]\\d{4}$/',
            'uan' => '/^81\\d{6}$/',
            'emergency' => '/^11[78]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4,8}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{4}$/',
            'voip' => '/^\\d{8}$/',
            'uan' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
