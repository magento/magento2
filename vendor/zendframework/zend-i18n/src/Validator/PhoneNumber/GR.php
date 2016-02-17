<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '30',
    'patterns' => array(
        'national' => array(
            'general' => '/^[26-9]\\d{9}$/',
            'fixed' => '/^2(?:1\\d{2}|2(?:3[1-8]|4[1-7]|5[1-4]|6[1-8]|7[1-5]|[289][1-9])|3(?:1\\d|2[1-57]|3[1-4]|[45][1-3]|7[1-7]|8[1-6]|9[1-79])|4(?:1\\d|2[1-8]|3[1-4]|4[13-5]|6[1-578]|9[1-5])|5(?:1\\d|[239][1-4]|4[124]|5[1-6])|6(?:1\\d|3[124]|4[1-7]|5[13-9]|[269][1-6]|7[14]|8[1-5])|7(?:1\\d|2[1-5]|3[1-6]|4[1-7]|5[1-57]|6[134]|9[15-7])|8(?:1\\d|2[1-5]|[34][1-4]|9[1-7]))\\d{6}$/',
            'mobile' => '/^69\\d{8}$/',
            'tollfree' => '/^800\\d{7}$/',
            'premium' => '/^90[19]\\d{7}$/',
            'shared' => '/^8(?:0[16]|12|25)\\d{7}$/',
            'personal' => '/^70\\d{8}$/',
            'emergency' => '/^1(?:00|12|66|99)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
