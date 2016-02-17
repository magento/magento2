<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '679',
    'patterns' => array(
        'national' => array(
            'general' => '/^[36-9]\\d{6}|0\\d{10}$/',
            'fixed' => '/^(?:3[0-5]|6[25-7]|8[58])\\d{5}$/',
            'mobile' => '/^(?:7[0-467]|8[367]|9[02346-9])\\d{5}$/',
            'tollfree' => '/^0800\\d{7}$/',
            'shortcode' => '/^0(?:04|1[34]|8[1-4])|1(?:0[1-3]|[25]9)|2[289]|30|[45]4|75|913$/',
            'emergency' => '/^91[17]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}(?:\\d{4})?$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{11}$/',
            'shortcode' => '/^\\d{2,3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
