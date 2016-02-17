<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '685',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-8]\\d{4,6}$/',
            'fixed' => '/^(?:[2-5]\\d|6[1-9]|84\\d{2})\\d{3}$/',
            'mobile' => '/^(?:60|7[25-7]\\d)\\d{4}$/',
            'tollfree' => '/^800\\d{3}$/',
            'emergency' => '/^99[4-6]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,7}$/',
            'fixed' => '/^\\d{5,7}$/',
            'mobile' => '/^\\d{6,7}$/',
            'tollfree' => '/^\\d{6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
