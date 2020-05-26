<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

//phpcs:ignore
$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navLandingTitles' => [
        'install'    => 'Magento',
    ],
    'navLanding' => [
        [
            'id'          => 'root',
            'step'        => 0,
            'views'       => ['root' => []],
        ],
        [
            'id'          => 'root.license',
            'url'         => 'license',
            'templateUrl' => "$base/license",
            'title'       => 'License',
            'main'        => true,
            'nav'         => false,
            'order'       => -1,
            'type'        => 'install'
        ],
        [
            'id'          => 'root.landing',
            'url'         => 'landing',
            'templateUrl' => "$base/landing",
            'title'       => 'Magento',
            'controller'  => 'landingController',
            'main'        => true,
            'default'     => true,
            'order'       => 0,
            'type'        => 'install'
        ],
    ],
];
