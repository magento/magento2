<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '509',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-489]\\d{7}$/',
            'fixed' => '/^2(?:[24]\\d|5[1-5]|94)\\d{5}$/',
            'mobile' => '/^(?:3[1-9]|4\\d)\\d{6}$/',
            'tollfree' => '/^8\\d{7}$/',
            'voip' => '/^98[89]\\d{5}$/',
            'shortcode' => '/^1\\d{2}$/',
            'emergency' => '/^11[48]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
