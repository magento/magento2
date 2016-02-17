<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '47',
    'patterns' => array(
        'national' => array(
            'general' => '/^0\\d{4}|[4789]\\d{7}$/',
            'fixed' => '/^79\\d{6}$/',
            'mobile' => '/^(?:4[015-8]|5[89]|9\\d)\\d{6}$/',
            'tollfree' => '/^80[01]\\d{5}$/',
            'premium' => '/^82[09]\\d{5}$/',
            'shared' => '/^810(?:0[0-6]|[2-8]\\d)\\d{3}$/',
            'personal' => '/^880\\d{5}$/',
            'voip' => '/^85[0-5]\\d{5}$/',
            'uan' => '/^0\\d{4}|81(?:0(?:0[7-9]|1\\d)|5\\d{2})\\d{3}$/',
            'voicemail' => '/^81[23]\\d{5}$/',
            'emergency' => '/^11[023]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5}(?:\\d{3})?$/',
            'fixed' => '/^\\d{8}$/',
            'mobile' => '/^\\d{8}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'shared' => '/^\\d{8}$/',
            'personal' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
            'voicemail' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
