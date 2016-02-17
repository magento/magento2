<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '43',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-9]\d{3,12}$/',
            'fixed' => '/^1\d{3,12}|(?:2(?:1[467]|2[13-8]|5[2357]|6[1-46-8]|7[1-8]|8[124-7]|9[1458])|3(?:1[1-8]|3[23568]|4[5-7]|5[1378]|6[1-38]|8[3-68])|4(?:2[1-8]|35|63|7[1368]|8[2457])|5(?:12|2[1-8]|3[357]|4[147]|5[12578]|6[37])|6(?:13|2[1-47]|4[1-35-8]|5[468]|62)|7(?:2[1-8]|3[25]|4[13478]|5[68]|6[16-8]|7[1-6]|9[45]))\d{3,10}$/',
            'mobile' => '/^6(?:44|5[0-3579]|6[013-9]|[7-9]\d)\d{4,10}$/',
            'tollfree' => '/^80[02]\d{6,10}$/',
            'premium' => '/^(?:711|9(?:0[01]|3[019]))\d{6,10}$/',
            'shared' => '/^8(?:10|2[018])\d{6,10}$/',
            'voip' => '/^780\d{6,10}$/',
            'uan' => '/^5(?:(?:0[1-9]|17)\d{2,10}|[79]\d{3,11})|720\d{6,10}$/',
            'emergency' => '/^1(?:[12]2|33|44)$/',
        ),
        'possible' => array(
            'general' => '/^\d{3,13}$/',
            'mobile' => '/^\d{7,13}$/',
            'tollfree' => '/^\d{9,13}$/',
            'premium' => '/^\d{9,13}$/',
            'shared' => '/^\d{9,13}$/',
            'voip' => '/^\d{9,13}$/',
            'uan' => '/^\d{5,13}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
