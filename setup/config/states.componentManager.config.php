<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$base = basename($_SERVER['SCRIPT_FILENAME']);

return [
    'navUpdaterTitles' => [
        'cm'    => 'Component Manager',
    ],
    'navUpdater' => [
        [
            'id'          => 'root.readiness-check-cm',
            'url'         => 'readiness-check-updater',
            'templateUrl' => "{$base}/readiness-check-updater",
            'title'       => "Readiness \n Check",
            'header'      => 'Step 1: Readiness Check',
            'nav'         => true,
            'order'       => 2,
            'type'        => 'cm'
        ],
        [
            'id'          => 'root.readiness-check-cm.progress',
            'url'         => 'readiness-check-updater/progress',
            'templateUrl' => "$base/readiness-check-updater/progress",
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav'         => false,
            'order'       => 3,
            'type'        => 'cm'
        ],
        [
            'id'          => 'root.create-backup-cm',
            'url'         => 'create-backup',
            'templateUrl' => "$base/create-backup",
            'title'       => "Create \n Backup",
            'header'      => 'Step 2: Create Backup',
            'controller'  => 'createBackupController',
            'nav'         => true,
            'validate'    => true,
            'order'       => 4,
            'type'        => 'cm'
        ],
        [
            'id'          => 'root.create-backup-cm.progress',
            'url'         => 'create-backup/progress',
            'templateUrl' => "$base/complete-backup/progress",
            'title'       => "Create \n Backup",
            'header'      => 'Step 2: Create Backup',
            'controller'  => 'completeBackupController',
            'nav'         => false,
            'order'       => 5,
            'type'        => 'cm'
        ],
        [
            'id'          => 'root.start-updater-cm',
            'url'         => 'start-updater',
            'templateUrl' => "$base/start-updater",
            'controller'  => 'startUpdaterController',
            'title'       => "Component \n Update",
            'header'      => 'Step 3: Component Update',
            'nav'         => true,
            'order'       => 6,
            'type'        => 'cm'
        ],
    ],
];
