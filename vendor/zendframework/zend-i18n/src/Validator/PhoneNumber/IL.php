<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '972',
    'patterns' => array(
        'national' => array(
            'general' => '/^[17]\\d{6,9}|[2-589]\\d{3}(?:\\d{3,6})?|6\\d{3}$/',
            'fixed' => '/^[2-489]\\d{7}$/',
            'mobile' => '/^5(?:[02347-9]\\d{2}|5(?:2[23]|3[34]|4[45]|5[5689]|6[67]|7[78]|8[89])|6[2-9]\\d)\\d{5}$/',
            'tollfree' => '/^1(?:80[019]\\d{3}|255)\\d{3}$/',
            'premium' => '/^1(?:212|(?:9(?:0[01]|19)|200)\\d{2})\\d{4}$/',
            'shared' => '/^1700\\d{6}$/',
            'voip' => '/^7(?:2[23]\\d|3[237]\\d|47\\d|6(?:5\\d|8[08])|7\\d{2}|8(?:33|55|77|81))\\d{5}$/',
            'uan' => '/^[2-689]\\d{3}$/',
            'voicemail' => '/^1599\\d{6}$/',
            'shortcode' => '/^1\\d{3}$/',
            'emergency' => '/^1(?:0[012]|12)$/',
        ),
        'possible' => array(
            'general' => '/^\\d{4,10}$/',
            'fixed' => '/^\\d{7,8}$/',
            'mobile' => '/^\\d{9}$/',
            'tollfree' => '/^\\d{7,10}$/',
            'premium' => '/^\\d{8,10}$/',
            'shared' => '/^\\d{10}$/',
            'voip' => '/^\\d{9}$/',
            'uan' => '/^\\d{4}$/',
            'voicemail' => '/^\\d{10}$/',
            'shortcode' => '/^\\d{4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
