<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '260',
    'patterns' => array(
        'national' => array(
            'general' => '/^[289]\\d{8}$/',
            'fixed' => '/^21[1-8]\\d{6}$/',
            'mobile' => '/^9(?:5[05]|6\\d|7[13-9])\\d{6}$/',
            'tollfree' => '/^800\\d{6}$/',
            'emergency' => '/^(?:112|99[139])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
