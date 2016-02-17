<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '595',
    'patterns' => array(
        'national' => array(
            'general' => '/^5[0-5]\\d{4,7}|[2-46-9]\\d{5,8}$/',
            'fixed' => '/^(?:[26]1|3[289]|4[124678]|7[123]|8[1236])\\d{5,7}|(?:2(?:2[4568]|7[15]|9[1-5])|3(?:18|3[167]|4[2357]|51)|4(?:18|2[45]|3[12]|5[13]|64|71|9[1-47])|5(?:[1-4]\\d|5[0234])|6(?:3[1-3]|44|7[1-4678])|7(?:17|4[0-4]|6[1-578]|75|8[0-8])|858)\\d{5,6}$/',
            'mobile' => '/^9(?:61|[78][1-6]|9[1-5])\\d{6}$/',
            'voip' => '/^8700[0-4]\\d{4}$/',
            'uan' => '/^[2-9]0\\d{4,7}$/',
            'shortcode' => '/^1[1-4]\\d$/',
            'emergency' => '/^128|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,9}$/',
            'mobile' => '/^\\d{9}$/',
            'voip' => '/^\\d{9}$/',
            'uan' => '/^\\d{6,9}$/',
            'shortcode' => '/^\\d{3}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
