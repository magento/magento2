<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navUpdaterTitles' => [
        'uninstall'    => 'Uninstall ',
    ],
    'navUpdater' => [
        [
            'id'          => 'root.readiness-check-uninstall',
            'url'         => 'readiness-check-uninstall',
            'templateUrl' => "{$base}/readiness-check-updater",
            'title'       => "Readiness \n Check",
            'header'      => 'Step 1: Readiness Check',
            'nav'         => true,
            'order'       => 2,
            'type'        => 'uninstall'
        ],
        [
            'id'          => 'root.readiness-check-uninstall.progress',
            'url'         => 'readiness-check-uninstall/progress',
            'templateUrl' => "$base/readiness-check-updater/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav'         => false,
            'order'       => 3,
            'type'        => 'uninstall'
        ],
        [
            'id'          => 'root.create-backup-uninstall',
            'url'         => 'create-backup',
            'templateUrl' => "$base/create-backup",
            'title'       => "Create \n Backup",
            'header'      => 'Step 2: Create Backup',
            'controller'  => 'createBackupController',
            'nav'         => true,
            'validate'    => true,
            'order'       => 4,
            'type'        => 'uninstall'
        ],
        [
            'id'          => 'root.create-backup-uninstall.progress',
            'url'         => 'create-backup/progress',
            'templateUrl' => "$base/complete-backup/progress",
            'title'       => "Create \n Backup",
            'header'      => 'Step 2: Create Backup',
            'controller'  => 'completeBackupController',
            'nav'         => false,
            'order'       => 5,
            'type'        => 'uninstall'
        ],
        [
            'id'          => 'root.data-option',
            'url'         => 'data-option',
            'templateUrl' => "$base/data-option",
            'title'       => "Remove or \n Keep Data",
            'controller'  => 'dataOptionController',
            'header'      => 'Step 3: Remove or Keep Data',
            'nav'         => true,
            'order'       => 6,
            'type'        => 'uninstall'
        ],
        [
            'id'          => 'root.start-updater-uninstall',
            'url'         => 'uninstall',
            'templateUrl' => "$base/start-updater",
            'title'       => "Uninstall",
            'controller'  => 'startUpdaterController',
            'header'      => 'Step 4: Uninstall',
            'nav'         => true,
            'order'       => 7,
            'type'        => 'uninstall'
        ],
        [
            'id'          => 'root.uninstall-success',
            'url'         => 'uninstall-success',
            'templateUrl' => "$base/updater-success",
            'controller'  => 'updaterSuccessController',
            'order'       => 8,
            'main'        => true,
            'noMenu'      => true
        ],
    ],
];
