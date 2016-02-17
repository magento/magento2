<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '355',
    'patterns' => array(
        'national' => array(
            'general' => '/^[2-57]\d{7}|6\d{8}|8\d{5,7}|9\d{5}$/',
            'fixed' => '/^(?:2(?:[168][1-9]|[247]\d|9[1-7])|3(?:1[1-3]|[2-6]\d|[79][1-8]|8[1-9])|4\d{2}|5(?:1[1-4]|[2-578]\d|6[1-5]|9[1-7])|8(?:[19][1-5]|[2-6]\d|[78][1-7]))\d{5}$/',
            'mobile' => '/^6[6-9]\d{7}$/',
            'tollfree' => '/^800\d{4}$/',
            'premium' => '/^900\d{3}$/',
            'shared' => '/^808\d{3}$/',
            'personal' => '/^700\d{5}$/',
            'emergency' => '/^12[789]$/',
        ),
        'possible' => array(
            'general' => '/^\d{5,9}$/',
            'fixed' => '/^\d{5,8}$/',
            'mobile' => '/^\d{9}$/',
            'tollfree' => '/^\d{7}$/',
            'premium' => '/^\d{6}$/',
            'shared' => '/^\d{6}$/',
            'personal' => '/^\d{8}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
