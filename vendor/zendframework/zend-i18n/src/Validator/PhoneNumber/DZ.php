<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '213',
    'patterns' => array(
        'national' => array(
            'general' => '/^(?:[1-4]|[5-9]\\d)\\d{7}$/',
            'fixed' => '/^(?:1\\d|2[014-79]|3[0-8]|4[0135689])\\d{6}|9619\\d{5}$/',
            'mobile' => '/^(?:5[56]|7[7-9])\\d{7}|6(?:[569]\\d|70)\\d{6}$/',
            'tollfree' => '/^800\\d{6}$/',
            'premium' => '/^80[3-689]1\\d{5}$/',
            'shared' => '/^80[12]1\\d{5}$/',
            'voip' => '/^98[23]\\d{6}$/',
            'emergency' => '/^1[47]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'shared' => '/^\\d{9}$/',
            'voip' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
