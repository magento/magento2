<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '599',
    'patterns' => array(
        'national' => array(
            'general' => '/^[169]\\d{6,7}$/',
            'fixed' => '/^9(?:[48]\\d{2}|50\\d|7(?:2[0-2]|[34]\\d|6[35-7]|77))\\d{4}$/',
            'mobile' => '/^9(?:5(?:[1246]\\d|3[01])|6(?:[1679]\\d|3[01]))\\d{4}$/',
            'pager' => '/^955\\d{5}$/',
            'shared' => '/^(?:10|69)\\d{5}$/',
            'emergency' => '/^112|911$/',
        ),
        'possible' => array(
            'general' => '/^\\d{7,8}$/',
            'shared' => '/^\\d{7}$/',
            'emergency' => '/^\\d{3}$/',
        ),
    ),
);
