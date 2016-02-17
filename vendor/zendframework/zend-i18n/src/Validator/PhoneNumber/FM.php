<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '691',
    'patterns' => array(
        'national' => array(
            'general' => '/^[39]\\d{6}$/',
            'fixed' => '/^3[2357]0[1-9]\\d{3}|9[2-6]\\d{5}$/',
            'mobile' => '/^3[2357]0[1-9]\\d{3}|9[2-7]\\d{5}$/',
            'emergency' => '/^911|320221$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}(?:\\d{3})?$/',
        ),
    ),
);
