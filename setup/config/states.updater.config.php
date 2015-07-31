<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navUpdaterTitles' => [
        'su'    => 'System Upgrade',
    ],
    'navUpdater' => [
        [
            'id'          => 'root.readiness-check-su',
            'url'         => 'readiness-check-updater',
            'templateUrl' => "$base/readiness-check-updater",
            'title'       => "Readiness \n Check",
            'header'      => 'Step 2: Readiness Check',
            'order'       => 2,
            'nav'         => true,
            'type'        => 'su'
        ],
        [
            'id'          => 'root.readiness-check-su.progress',
            'url'         => 'readiness-check-updater/progress',
            'templateUrl' => "$base/readiness-check-updater/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 2: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav'         => false,
            'order'       => 3,
            'type'        => 'su'
        ],
        [
            'id'          => 'root.create-backup-su',
            'url'         => 'create-backup',
            'templateUrl' => "$base/create-backup",
            'title'       => 'Create Backup',
            'controller'  => 'createBackupController',
            'header'      => 'Step 3: Create Backup',
            'order'       => 4,
            'nav'         => true,
            'type'        => 'su'
        ],
        [
            'id'          => 'root.create-backup-su.progress',
            'url'         => 'create-backup/progress',
            'templateUrl' => "$base/complete-backup/progress",
            'title'       => "Create \n Backup",
            'header'      => 'Step 3: Create Backup',
            'controller'  => 'completeBackupController',
            'nav'         => false,
            'order'       => 5,
            'type'        => 'su'
        ],
        [
            'id'          => 'root.start-updater-su',
            'url'         => 'start-updater',
            'templateUrl' => "$base/start-updater",
            'title'       => "System \n Upgrade",
            'controller'  => 'startUpdaterController',
            'header'      => 'Step 4: System Upgrade',
            'order'       => 6,
            'nav'         => true,
            'type'        => 'su'
        ],
        [
            'id'          => 'root.updater-success',
            'url'         => 'updater-success',
            'templateUrl' => "$base/updater-success",
            'controller'  => 'updaterSuccessController',
            'order'       => 7,
            'noMenu'      => true
        ],
    ],
];
