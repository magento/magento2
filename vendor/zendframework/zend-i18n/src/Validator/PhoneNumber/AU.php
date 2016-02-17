<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '61',
    'patterns' => array(
        'national' => array(
            'general' => '/^[1-578]\d{5,9}$/',
            'fixed' => '/^[237]\d{8}|8(?:[68]\d{3}|7[0-69]\d{2}|9(?:[02-9]\d{2}|1(?:[0-57-9]\d|6[0135-9])))\d{4}$/',
            'mobile' => '/^14(?:5\d|71)\d{5}|4(?:[0-2]\d|3[0-57-9]|4[47-9]|5[0-35-9]|6[6-9]|[79][07-9]|8[17-9])\d{6}$/',
            'pager' => '/^16\d{3,7}$/',
            'tollfree' => '/^180(?:0\d{3}|2)\d{3}$/',
            'premium' => '/^19(?:0[0126]\d{6}|[13-5]\d{3}|[679]\d{5})$/',
            'shared' => '/^13(?:00\d{2})?\d{4}$/',
            'personal' => '/^500\d{6}$/',
            'voip' => '/^550\d{6}$/',
            'emergency' => '/^000|112$/',
        ),
        'possible' => array(
            'general' => '/^\d{6,10}$/',
            'fixed' => '/^\d{8,9}$/',
            'mobile' => '/^\d{9}$/',
            'pager' => '/^\d{5,9}$/',
            'tollfree' => '/^\d{7,10}$/',
            'premium' => '/^\d{6,10}$/',
            'shared' => '/^\d{6,10}$/',
            'personal' => '/^\d{9}$/',
            'voip' => '/^\d{9}$/',
            'emergency' => '/^\d{3}$/',
        ),
    ),
);
