<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '241',
    'patterns' => array(
        'national' => array(
            'general' => '/^[01]\\d{6,7}$/',
            'fixed' => '/^1\\d{6}$/',
            'mobile' => '/^0[2-7]\\d{6}$/',
            'emergency' => '/^1730|18|13\\d{2}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}$/',
            'emergency' => '/^\\d{2,4}$/',
        ),
    ),
);
