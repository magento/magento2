<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '975',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-8]\\d{6,7}$/',
            'fixed' => '/^(?:2[3-6]|[34][5-7]|5[236]|6[2-46]|7[246]|8[2-4])\\d{5}$/',
            'mobile' => '/^[17]7\\d{6}$/',
            'emergency' => '/^11[023]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,8}$/',
            'fixed' => '/^\\d{6,7}$/',
            'mobile' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
