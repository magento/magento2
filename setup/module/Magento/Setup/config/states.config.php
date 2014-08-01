<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return [
    'nav' => [
        [
            'id'          => 'root',
            'step'        => 0,
            'views'       => ['root' => []]
        ],
        [
            'id'          => 'root.landing',
            'url'         => 'landing',
            'templateUrl' => 'landing',
            'title'       => 'Landing',
            'controller'  => 'landingController',
            'main'        => true,
            'default'     => true,
            'order'       => 0,
        ],
        [
            'id'          => 'root.readiness-check',
            'url'         => 'readiness-check',
            'templateUrl' => 'readiness-check',
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'nav-bar'     => true,
            'order'       => 1,
        ],
        [
            'id'          => 'root.readiness-check.progress',
            'url'         => 'readiness-check/progress',
            'templateUrl' => 'readiness-check/progress',
            'title'       => 'Readiness Check',
            'header'      => 'Step 1: Readiness Check',
            'controller'  => 'readinessCheckController',
            'nav-bar'     => false,
            'order'       => 2,
        ],
        [
            'id'          => 'root.add-database',
            'url'         => 'add-database',
            'templateUrl' => 'add-database',
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
            'templateUrl' => 'web-configuration',
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
            'templateUrl' => 'customize-your-store',
            'title'       => 'Customize Your Store',
            'header'      => 'Step 4: Customize Your Store',
            'controller'  => 'customizeYourStoreController',
            'nav-bar'     => true,
            'order'       => 5,
        ],
        [
            'id'          => 'root.create-admin-account',
            'url'         => 'create-admin-account',
            'templateUrl' => 'create-admin-account',
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
            'templateUrl' => 'install',
            'title'       => 'Install',
            'header'      => 'Step 6: Install',
            'controller'  => 'installController',
            'nav-bar'     => true,
            'order'       => 7,
        ],
        [
            'id'          => 'root.success',
            'url'         => 'success',
            'templateUrl' => 'success',
            'controller'  => 'successController',
            'main'        => true,
            'order'       => 8,
        ],
    ]
];
