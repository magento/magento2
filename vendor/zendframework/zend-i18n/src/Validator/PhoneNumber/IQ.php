<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '964',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-7]\\d{7,9}$/',
            'fixed' => '/^1\\d{7}|(?:2[13-5]|3[02367]|4[023]|5[03]|6[026])\\d{6,7}$/',
            'mobile' => '/^7[3-9]\\d{8}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{6,10}$/',
            'fixed' => '/^\\d{6,9}$/',
            'mobile' => '/^\\d{10}$/',
        ),
    ),
);
