<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '974',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-8]\\d{6,7}$/',
            'fixed' => '/^4[04]\\d{6}$/',
            'mobile' => '/^[3567]\\d{7}$/',
            'pager' => '/^2(?:[12]\\d|61)\\d{4}$/',
            'tollfree' => '/^800\\d{4}$/',
            'shortcode' => '/^(?:1|20|9[27]\\d)\\d{2}$/',
            'emergency' => '/^999$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}$/',
            'pager' => '/^\\d{7}$/',
            'shortcode' => '/^\\d{3,4}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
