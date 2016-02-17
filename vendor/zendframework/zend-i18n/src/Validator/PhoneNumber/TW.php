<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '886',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{7,8}$/',
            'fixed' => '/^[2-8]\\d{7,8}$/',
            'mobile' => '/^9\\d{8}$/',
            'tollfree' => '/^800\\d{6}$/',
            'premium' => '/^900\\d{6}$/',
            'emergency' => '/^11[029]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
