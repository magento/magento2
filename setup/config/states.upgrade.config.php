<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navUpgrader' => [
        [
            'id'          => 'root',
            'step'        => 0,
            'views'       => ['root' => []],
        ],
        [
            'id'          => 'root.home',
            'url'         => 'home',
            'templateUrl' => "$base/home",
            'title'       => 'Home',
            'controller'  => 'homeController',
            'default'     => true,
            'order'       => 0,
        ],
        [
            'id'          => 'root.system-upgrade',
            'url'         => 'system-upgrade',
            'templateUrl' => "{$base}/system-upgrade",
            'title'       => 'Home',
            'controller'  => 'systemUpgradeController',
            'header'      => 'Step 1: Select Version',
            'order'       => 1,
            'nav-bar'     => true
        ]
    ]
];
