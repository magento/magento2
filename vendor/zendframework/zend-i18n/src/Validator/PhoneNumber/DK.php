<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '45',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{7}$/',
            'fixed' => '/^(?:[2-7]\\d|8[126-9]|9[126-9])\\d{6}$/',
            'mobile' => '/^(?:[2-7]\\d|8[126-9]|9[126-9])\\d{6}$/',
            'tollfree' => '/^80\\d{6}$/',
            'premium' => '/^90\\d{6}$/',
            'emergency' => '/^112$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
