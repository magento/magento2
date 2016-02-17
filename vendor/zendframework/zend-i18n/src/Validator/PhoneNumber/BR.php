<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '55',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-46-9]\\d{7,10}|5\\d{8,9}$/',
            'fixed' => '/^1[1-9][2-5]\\d{7}|(?:[4689][1-9]|2[12478]|3[1-578]|5[13-5]|7[13-579])[2-5]\\d{7}$/',
            'mobile' => '/^1(?:1(?:5[347]|[6-8]\\d|9\\d{1,2})|[2-9][6-9]\\d)\\d{6}|(?:[4689][1-9]|2[12478]|3[1-578]|5[13-5]|7[13-579])[6-9]\\d{7}$/',
            'tollfree' => '/^800\\d{6,7}$/',
            'premium' => '/^[359]00\\d{6,7}$/',
            'shared' => '/^[34]00\\d{5}$/',
            'emergency' => '/^1(?:12|28|9[023])|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,11}$/',
            'mobile' => '/^\\d{10,11}$/',
            'shared' => '/^\\d{8}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
