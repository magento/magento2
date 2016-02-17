<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '382',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{7,8}$/',
            'fixed' => '/^(?:20[2-8]|3(?:0[2-7]|1[35-7]|2[3567]|3[4-7])|4(?:0[237]|1[27])|5(?:0[47]|1[27]|2[378]))\\d{5}$/',
            'mobile' => '/^6(?:32\\d|[89]\\d{2}|7(?:[0-8]\\d|9(?:[3-9]|[0-2]\\d)))\\d{4}$/',
            'tollfree' => '/^800[28]\\d{4}$/',
            'premium' => '/^(?:88\\d|9(?:4[13-8]|5[16-8]))\\d{5}$/',
            'voip' => '/^78[1-9]\\d{5}$/',
            'uan' => '/^77\\d{6}$/',
            'shortcode' => '/^1(?:16\\d{3}|2(?:[015-9]|\\d{2})|[0135]\\d{2}|4\\d{2,3}|9\\d{3})$/',
            'emergency' => '/^1(?:12|2[234])$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,9}$/',
            'fixed' => '/^\\d{6,8}$/',
            'mobile' => '/^\\d{8,9}$/',
            'tollfree' => '/^\\d{8}$/',
            'premium' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
            'uan' => '/^\\d{8}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
