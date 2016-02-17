<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '44',
    'patterns' => array(
        'national' => array(
            'general' => '/^[135789]\\d{6,9}$/',
            'fixed' => '/^1481\\d{6}$/',
            'mobile' => '/^7(?:781|839|911)\\d{6}$/',
            'pager' => '/^76(?:0[012]|2[356]|4[0134]|5[49]|6[0-369]|77|81|9[39])\\d{6}$/',
            'tollfree' => '/^80(?:0(?:1111|\\d{6,7})|8\\d{7})|500\\d{6}$/',
            'premium' => '/^(?:87[123]|9(?:[01]\\d|8[0-3]))\\d{7}$/',
            'shared' => '/^8(?:4(?:5464\\d|[2-5]\\d{7})|70\\d{7})$/',
            'personal' => '/^70\\d{8}$/',
            'voip' => '/^56\\d{8}$/',
            'uan' => '/^(?:3[0347]|55)\\d{8}$/',
            'shortcode' => '/^1(?:0[01]|1(?:1|[68]\\d{3})|23|4(?:1|7\\d)|55|800\\d|95)$/',
            'emergency' => '/^112|999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'mobile' => '/^\\d{10}$/',
            'pager' => '/^\\d{10}$/',
            'tollfree' => '/^\\d{7}(?:\\d{2,3})?$/',
            'premium' => '/^\\d{10}$/',
            'shared' => '/^\\d{7}(?:\\d{3})?$/',
            'personal' => '/^\\d{10}$/',
            'voip' => '/^\\d{10}$/',
            'uan' => '/^\\d{10}$/',
            'shortcode' => '/^\\d{3,6}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
