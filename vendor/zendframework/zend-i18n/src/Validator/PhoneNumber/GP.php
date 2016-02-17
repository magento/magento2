<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '590',
    'patterns' => array(
        'national' => array(
            'general' => '/^[56]\\d{8}$/',
            'fixed' => '/^590(?:1[12]|2[0-68]|3[28]|4[126-8]|5[067]|6[018]|[89]\\d)\\d{4}$/',
            'mobile' => '/^690(?:00|1[1-9]|2[013-5]|[3-5]\\d|6[0-57-9]|7[1-6]|8[0-6]|9[09])\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
