<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '246',
    'patterns' => array(
        'national' => array(
            'general' => '/^3\\d{6}$/',
            'fixed' => '/^37\\d{5}$/',
            'mobile' => '/^38\\d{5}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
        ),
    ),
);
