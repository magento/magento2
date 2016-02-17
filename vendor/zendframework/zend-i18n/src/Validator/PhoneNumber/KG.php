<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '996',
    'patterns' => array(
        'national' => array(
            'general' => '/^[35-8]\\d{8,9}$/',
            'fixed' => '/^(?:3(?:1(?:2\\d|3[1-9]|47|5[02]|6[1-8])|2(?:22|3[0-479]|6[0-7])|4(?:22|5[6-9]|6[0-4])|5(?:22|3[4-7]|59|6[0-5])|6(?:22|5[35-7]|6[0-3])|7(?:22|3[468]|4[1-9]|59|6\\d|7[5-7])|9(?:22|4[1-8]|6[0-8]))|6(?:09|12|2[2-4])\\d)\\d{5}$/',
            'mobile' => '/^5[124-7]\\d{7}|7(?:0[0-357-9]|7\\d)\\d{6}$/',
            'tollfree' => '/^800\\d{6,7}$/',
            'emergency' => '/^10[123]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{5,10}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{9,10}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
