<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '94',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-9]\\d{8}$/',
            'fixed' => '/^(?:[189]1|2[13-7]|3[1-8]|4[157]|5[12457]|6[35-7])[2-57]\\d{6}$/',
            'mobile' => '/^7[125-8]\\d{7}$/',
            'emergency' => '/^11[0189]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
