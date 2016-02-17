<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '264',
    'patterns' => array(
        'national' => array(
            'general' => '/^[68]\\d{7,8}$/',
            'fixed' => '/^6(?:1(?:17|2(?:[0189]\\d|[2-6]|7\\d?)|3(?:2\\d|3[378])|4[01]|69|7[014])|2(?:17|25|5(?:[0-36-8]|4\\d?)|69|70)|3(?:17|2(?:[0237]\\d?|[14-689])|34|6[29]|7[01]|81)|4(?:17|2(?:[012]|7?)|4(?:[06]|1\\d)|5(?:[01357]|[25]\\d?)|69|7[01])|5(?:17|2(?:[0459]|[23678]\\d?)|69|7[01])|6(?:17|2(?:5|6\\d?)|38|42|69|7[01])|7(?:17|2(?:[569]|[234]\\d?)|3(?:0\\d?|[13])|69|7[01]))\\d{4}$/',
            'mobile' => '/^(?:60|8[125])\\d{7}$/',
            'premium' => '/^8701\\d{5}$/',
            'voip' => '/^8(3\\d{2}|86)\\d{5}$/',
            'shortcode' => '/^1\\d{3}|9(?:3111|\\d{2})$/',
            'emergency' => '/^10111$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,9}$/',
            'mobile' => '/^\\d{9}$/',
            'premium' => '/^\\d{9}$/',
            'shortcode' => '/^\\d{3,5}$/',
            'emergency' => '/^\\d{5}$/',
        ),
    ),
);
