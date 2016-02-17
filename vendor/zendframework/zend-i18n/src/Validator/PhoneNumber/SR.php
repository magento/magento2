<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '597',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-8]\\d{5,6}$/',
            'fixed' => '/^(?:2[1-3]|3[0-7]|4\\d|5[2-58]|68\\d)\\d{4}$/',
            'mobile' => '/^(?:7[1-57]|8[1-9])\\d{5}$/',
            'voip' => '/^56\\d{4}$/',
            'shortcode' => '/^1(?:[02-9]\\d|1[0-46-9]|\\d{3})$/',
            'emergency' => '/^115$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,7}$/',
            'mobile' => '/^\\d{7}$/',
            'voip' => '/^\\d{6}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
