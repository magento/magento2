<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '258',
    'patterns' => array(
        'national' => array(
            'general' => '/^[28]\\d{7,8}$/',
            'fixed' => '/^2(?:[1346]\\d|5[0-2]|[78][12]|93)\\d{5}$/',
            'mobile' => '/^8[246]\\d{7}$/',
            'tollfree' => '/^800\\d{6}$/',
            'shortcode' => '/^1[0234]\\d$/',
            'emergency' => '/^1(?:1[79]|9[78])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
