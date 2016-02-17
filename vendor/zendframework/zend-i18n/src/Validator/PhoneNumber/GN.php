<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '224',
    'patterns' => array(
        'national' => array(
            'general' => '/^[23567]\\d{7,8}$/',
            'fixed' => '/^30(?:24|3[12]|4[1-35-7]|5[13]|6[189]|[78]1|9[1478])\\d{4}$/',
            'mobile' => '/^(?:24|55)\\d{6}|6(?:0(?:2[0-35-9]|3[3467]|5[2457-9])|1[0-5]\\d|2\\d{2,3}|[4-9]\\d{2}|3(?:[14]0|35))\\d{4}$/',
            'voip' => '/^78\\d{6}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{8,9}$/',
            'fixed' => '/^\\d{8}$/',
            'voip' => '/^\\d{8}$/',
        ),
    ),
);
