<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '968',
    'patterns' => array(
        'national' => array(
            'general' => '/^(?:2[2-6]|5|9[1-9])\\d{6}|800\\d{5,6}$/',
            'fixed' => '/^2[2-6]\\d{6}$/',
            'mobile' => '/^9[1-9]\\d{6}$/',
            'tollfree' => '/^8007\\d{4,5}|500\\d{4}$/',
            'emergency' => '/^9999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{7,9}$/',
            'emergency' => '/^\\d{4}$/',
        ),
    ),
);
