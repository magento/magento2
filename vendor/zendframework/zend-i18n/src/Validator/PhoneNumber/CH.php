<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '41',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{8}|860\\d{9}$/',
            'fixed' => '/^(?:2[12467]|3[1-4]|4[134]|5[12568]|6[12]|[7-9]1)\\d{7}$/',
            'mobile' => '/^7[46-9]\\d{7}$/',
            'tollfree' => '/^800\\d{6}$/',
            'premium' => '/^90[016]\\d{6}$/',
            'shared' => '/^84[0248]\\d{6}$/',
            'personal' => '/^878\\d{6}$/',
            'voicemail' => '/^860\\d{9}$/',
            'emergency' => '/^1(?:1[278]|44)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}(?:\\d{3})?$/',
            'fixed' => '/^\\d{9}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'shared' => '/^\\d{9}$/',
            'personal' => '/^\\d{9}$/',
            'voicemail' => '/^\\d{12}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
