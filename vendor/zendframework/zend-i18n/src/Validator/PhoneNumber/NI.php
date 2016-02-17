<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '505',
    'patterns' => array(
        'national' => array(
            'general' => '/^[128]\\d{7}$/',
            'fixed' => '/^2\\d{7}$/',
            'mobile' => '/^[578]\\d{7}$/',
            'tollfree' => '/^1800\\d{4}$/',
            'emergency' => '/^118$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
