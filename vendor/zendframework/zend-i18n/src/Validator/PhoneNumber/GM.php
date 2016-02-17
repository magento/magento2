<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '220',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-9]\\d{6}$/',
            'fixed' => '/^(?:4(?:[23]\\d{2}|4(?:1[024679]|[6-9]\\d))|5(?:54[0-7]|6(?:[67]\\d)|7(?:1[04]|2[035]|3[58]|48))|8\\d{3})\\d{3}$/',
            'mobile' => '/^(?:2[0-2]|[3679]\\d)\\d{5}$/',
            'emergency' => '/^1?1[678]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{2,3}$/',
        ),
    ),
);
