<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '248',
    'patterns' => array(
        'national' => array(
            'general' => '/^[24689]\\d{5,6}$/',
            'fixed' => '/^4[2-46]\\d{5}$/',
            'mobile' => '/^2[5-8]\\d{5}$/',
            'tollfree' => '/^8000\\d{2}$/',
            'premium' => '/^98\\d{4}$/',
            'voip' => '/^64\\d{5}$/',
            'shortcode' => '/^1(?:0\\d|1[027]|2[0-8]|3[13]|4[0-2]|[59][15]|6[1-9]|7[124-6]|8[158])|96\\d{2}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,7}$/',
            'fixed' => '/^\\d{7}$/',
            'mobile' => '/^\\d{7}$/',
            'tollfree' => '/^\\d{6}$/',
            'premium' => '/^\\d{6}$/',
            'voip' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
