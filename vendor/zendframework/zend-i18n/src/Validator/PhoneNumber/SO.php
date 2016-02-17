<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '252',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-79]\\d{6,8}$/',
            'fixed' => '/^(?:[134]\\d|2[0-79]|5[57-9])\\d{5}$/',
            'mobile' => '/^(?:15\\d|2(?:4\\d|8)|6[17-9]?\\d{2}|7\\d{2}|9[01]\\d)\\d{5}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'fixed' => '/^\\d{7}$/',
        ),
    ),
);
