<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '243',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-6]\\d{6}|8\\d{6,8}|9\\d{8}$/',
            'fixed' => '/^[1-6]\\d{6}$/',
            'mobile' => '/^8(?:[0-259]\\d{2}|[48])\\d{5}|9[7-9]\\d{7}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,9}$/',
            'fixed' => '/^\\d{7}$/',
        ),
    ),
);
