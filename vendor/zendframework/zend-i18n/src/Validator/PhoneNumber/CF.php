<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '236',
    'patterns' => array(
        'national' => array(
            'general' => '/^[278]\\d{7}$/',
            'fixed' => '/^2[12]\\d{6}$/',
            'mobile' => '/^7[0257]\\d{6}$/',
            'premium' => '/^8776\\d{4}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8}$/',
        ),
    ),
);
