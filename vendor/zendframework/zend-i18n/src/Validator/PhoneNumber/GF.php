<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '594',
    'patterns' => array(
        'national' => array(
            'general' => '/^[56]\\d{8}$/',
            'fixed' => '/^594(?:10|2[012457-9]|3[0-57-9]|4[3-9]|5[7-9]|6[0-3]|9[014])\\d{4}$/',
            'mobile' => '/^694(?:[04][0-7]|1[0-5]|2[0-46-9]|38|9\\d)\\d{4}$/',
            'emergency' => '/^1[578]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
            'emergency' => '/^\\d{2}$/',
        ),
    ),
);
