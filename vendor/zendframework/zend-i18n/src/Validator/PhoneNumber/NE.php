<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '227',
    'patterns' => array(
        'national' => array(
            'general' => '/^[029]\\d{7}$/',
            'fixed' => '/^2(?:0(?:20|3[1-7]|4[134]|5[14]|6[14578]|7[1-578])|1(?:4[145]|5[14]|6[14-68]|7[169]|88))\\d{4}$/',
            'mobile' => '/^9[0-46-9]\\d{6}$/',
            'tollfree' => '/^08\\d{6}$/',
            'premium' => '/^09\\d{6}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
        ),
    ),
);
