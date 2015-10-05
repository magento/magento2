<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navInstallerTitles' => [
        'installer'    => 'Magento Installer',
    ],
    'navInstaller' => [
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
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.landing-installer',
            'url'         => 'landing-installer',
            'templateUrl' => "$base/landing-installer",
            'title'       => 'Landing',
            'controller'  => 'landingController',
            'main'        => true,
            'default'     => true,
            'order'       => 0,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.readiness-check-installer',
            'url'         => 'readiness-check-installer',
            'templateUrl' => "{$base}/readiness-check-installer",
            'title'       => "Readiness \n Check",
            'header'      => 'Step 1: Readiness Check',
            'nav'         => true,
            'order'       => 1,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.readiness-check-installer.progress',
            'url'         => 'readiness-check-installer/progress',
            'templateUrl' => "{$base}/readiness-check-installer/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav'         => false,
            'order'       => 2,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.add-database',
            'url'         => 'add-database',
            'templateUrl' => "{$base}/add-database",
            'title'       => "Add \n a Database",
            'header'      => 'Step 2: Add a Database',
            'controller'  => 'addDatabaseController',
            'nav'         => true,
            'validate'    => true,
            'order'       => 3,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.web-configuration',
            'url'         => 'web-configuration',
            'templateUrl' => "{$base}/web-configuration",
            'title'       => "Web \n Configuration",
            'header'      => 'Step 3: Web Configuration',
            'controller'  => 'webConfigurationController',
            'nav'         => true,
            'validate'    => true,
            'order'       => 4,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.customize-your-store',
            'url'         => 'customize-your-store',
            'templateUrl' => "{$base}/customize-your-store",
            'title'       => "Customize \n Your Store",
            'header'      => 'Step 4: Customize Your Store',
            'controller'  => 'customizeYourStoreController',
            'nav'         => true,
            'order'       => 5,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.create-admin-account',
            'url'         => 'create-admin-account',
            'templateUrl' => "{$base}/create-admin-account",
            'title'       => "Create \n Admin Account",
            'header'      => 'Step 5: Create Admin Account',
            'controller'  => 'createAdminAccountController',
            'nav'         => true,
            'validate'    => true,
            'order'       => 6,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.install',
            'url'         => 'install',
            'templateUrl' => "{$base}/install",
            'title'       => 'Install',
            'header'      => 'Step 6: Install',
            'controller'  => 'installController',
            'nav'         => true,
            'order'       => 7,
            'type'        => 'installer'
        ],
        [
            'id'          => 'root.success',
            'url'         => 'success',
            'templateUrl' => "{$base}/success",
            'controller'  => 'successController',
            'main'        => true,
            'order'       => 8,
            'type'        => 'installer'
        ],
    ],
];
