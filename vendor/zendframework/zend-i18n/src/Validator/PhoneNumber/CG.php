<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'code' => '242',
    'patterns' => array(
        'national' => array(
            'general' => '/^[028]\\d{8}$/',
            'fixed' => '/^222[1-589]\\d{5}$/',
            'mobile' => '/^0[14-6]\\d{7}$/',
            'tollfree' => '/^800\\d{6}$/',
        ),
        'possible' => array(
            'general' => '/^\\d{9}$/',
        ),
    ),
);
