<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '245',
    'patterns' => array(
        'national' => array(
            'general' => '/^[3567]\\d{6}$/',
            'fixed' => '/^3(?:2[0125]|3[1245]|4[12]|5[1-4]|70|9[1-467])\\d{4}$/',
            'mobile' => '/^[5-7]\\d{6}$/',
            'emergency' => '/^11[378]$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
