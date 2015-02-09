<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'nav' => [
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
            'nav-bar'     => false,
            'order'       => -1,
        ],
        [
            'id'          => 'root.landing',
            'url'         => 'landing',
            'templateUrl' => "$base/landing",
            'title'       => 'Landing',
            'controller'  => 'landingController',
            'main'        => true,
            'default'     => true,
            'order'       => 0,
        ],
        [
            'id'          => 'root.readiness-check',
            'url'         => 'readiness-check',
            'templateUrl' => "{$base}/readiness-check",
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'nav-bar'     => true,
            'order'       => 1,
        ],
        [
            'id'          => 'root.readiness-check.progress',
            'url'         => 'readiness-check/progress',
            'templateUrl' => "{$base}/readiness-check/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav-bar'     => false,
            'order'       => 2,
        ],
        [
            'id'          => 'root.add-database',
            'url'         => 'add-database',
            'templateUrl' => "{$base}/add-database",
            'title'       => 'Add a Database',
            'header'      => 'Step 2: Add a Database',
            'controller'  => 'addDatabaseController',
            'nav-bar'     => true,
            'validate'    => true,
            'order'       => 3,
        ],
        [
            'id'          => 'root.web-configuration',
            'url'         => 'web-configuration',
            'templateUrl' => "{$base}/web-configuration",
            'title'       => 'Web Configuration',
            'header'      => 'Step 3: Web Configuration',
            'controller'  => 'webConfigurationController',
            'nav-bar'     => true,
            'validate'    => true,
            'order'       => 4,
        ],
        [
            'id'          => 'root.customize-your-store',
            'url'         => 'customize-your-store',
            'templateUrl' => "{$base}/customize-your-store",
            'title'       => 'Customize Your Store',
            'header'      => 'Step 4: Customize Your Store',
            'controller'  => 'customizeYourStoreController',
            'nav-bar'     => true,
            'order'       => 5,
        ],
        [
            'id'          => 'root.create-admin-account',
            'url'         => 'create-admin-account',
            'templateUrl' => "{$base}/create-admin-account",
            'title'       => 'Create Admin Account',
            'header'      => 'Step 5: Create Admin Account',
            'controller'  => 'createAdminAccountController',
            'nav-bar'     => true,
            'validate'    => true,
            'order'       => 6,
        ],
        [
            'id'          => 'root.install',
            'url'         => 'install',
            'templateUrl' => "{$base}/install",
            'title'       => 'Install',
            'header'      => 'Step 6: Install',
            'controller'  => 'installController',
            'nav-bar'     => true,
            'order'       => 7,
        ],
        [
            'id'          => 'root.success',
            'url'         => 'success',
            'templateUrl' => "{$base}/success",
            'controller'  => 'successController',
            'main'        => true,
            'order'       => 8,
        ],
    ]
];
