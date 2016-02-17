<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '994',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-9]\\d{8}$/',
            'fixed' => '/^(?:1[28]\\d|2(?:02|1[24]|2[2-4]|33|[45]2|6[23])|365)\\d{6}$/',
            'mobile' => '/^(?:4[04]|5[015]|60|7[07])\\d{7}$/',
            'tollfree' => '/^88\\d{7}$/',
            'premium' => '/^900200\\d{3}$/',
            'emergency' => '/^1(?:0[123]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
