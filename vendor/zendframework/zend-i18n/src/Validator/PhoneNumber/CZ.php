<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '420',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-8]\\d{8}|9\\d{8,11}$/',
            'fixed' => '/^2\\d{8}|(?:3[1257-9]|4[16-9]|5[13-9])\\d{7}$/',
            'mobile' => '/^(?:60[1-8]|7(?:0[2-5]|[2379]\\d))\\d{6}$/',
            'tollfree' => '/^800\\d{6}$/',
            'premium' => '/^9(?:0[05689]|76)\\d{6}$/',
            'shared' => '/^8[134]\\d{7}$/',
            'personal' => '/^70[01]\\d{6}$/',
            'voip' => '/^9[17]0\\d{6}$/',
            'uan' => '/^9(?:5[056]|7[234])\\d{6}$/',
            'voicemail' => '/^9(?:3\\d{9}|6\\d{7,10})$/',
            'shortcode' => '/^1(?:1(?:6\\d{3}|8\\d)|2\\d{2,3}|3\\d{3,4}|4\\d{3}|99)$/',
            'emergency' => '/^1(?:12|5[058])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9,12}$/',
            'voicemail' => '/^\\d{9,12}$/',
            'shortcode' => '/^\\d{4,6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
