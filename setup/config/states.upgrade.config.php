<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navUpdaterTitles' => [
        'upgrade'    => 'System Upgrade',
    ],
    'navUpdater' => [
        [
            'id'          => 'root.select-version',
            'url'         => 'select-version',
            'templateUrl' => "$base/select-version",
            'title'       => 'System Upgrade',
            'controller'  => 'selectVersionController',
            'header'      => 'Step 1: Select Version',
            'order'       => 2,
            'nav'         => true,
            'type'        => 'upgrade'
        ],
        [
            'id'          => 'root.readiness-check-upgrade',
            'url'         => 'readiness-check-updater',
            'templateUrl' => "$base/readiness-check-updater",
            'title'       => "Readiness \n Check",
            'header'      => 'Step 2: Readiness Check',
            'order'       => 3,
            'nav'         => true,
            'type'        => 'upgrade'
        ],
        [
            'id'          => 'root.readiness-check-upgrade.progress',
            'url'         => 'readiness-check-updater/progress',
            'templateUrl' => "$base/readiness-check-updater/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 2: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav'         => false,
            'order'       => 4,
            'type'        => 'upgrade'
        ],
        [
            'id'          => 'root.create-backup-upgrade',
            'url'         => 'create-backup',
            'templateUrl' => "$base/create-backup",
            'title'       => 'Create Backup',
            'controller'  => 'createBackupController',
            'header'      => 'Step 3: Create Backup',
            'order'       => 5,
            'nav'         => true,
            'type'        => 'upgrade'
        ],
        [
            'id'          => 'root.create-backup-upgrade.progress',
            'url'         => 'create-backup/progress',
            'templateUrl' => "$base/complete-backup/progress",
            'title'       => "Create \n Backup",
            'header'      => 'Step 3: Create Backup',
            'controller'  => 'completeBackupController',
            'nav'         => false,
            'order'       => 6,
            'type'        => 'upgrade'
        ],
        [
            'id'          => 'root.start-updater-upgrade',
            'url'         => 'start-updater',
            'templateUrl' => "$base/start-updater",
            'title'       => "System \n Upgrade",
            'controller'  => 'startUpdaterController',
            'header'      => 'Step 4: System Upgrade',
            'order'       => 7,
            'nav'         => true,
            'type'        => 'upgrade'
        ],
        [
            'id'          => 'root.updater-success',
            'url'         => 'updater-success',
            'templateUrl' => "$base/updater-success",
            'controller'  => 'updaterSuccessController',
            'order'       => 8,
            'noMenu'      => true
        ],
        [
            'id'          => 'root.system-config',
            'url'         => 'system-config',
            'templateUrl' => "$base/system-config",
            'title'       => 'System config',
            'controller'  => 'systemConfigController',
            'default'     => false,
            'nav-bar'     => false,
            'noMenu'      => true,
            'order'       => -1,
        ]
    ],
];
