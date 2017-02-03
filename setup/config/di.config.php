<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'di' => [
        'allowed_controllers' => [
            'Magento\Setup\Controller\Index',
            'Magento\Setup\Controller\LandingInstaller',
            'Magento\Setup\Controller\LandingUpdater',
            'Magento\Setup\Controller\CreateBackup',
            'Magento\Setup\Controller\CompleteBackup',
            'Magento\Setup\Controller\Navigation',
            'Magento\Setup\Controller\Home',
            'Magento\Setup\Controller\SelectVersion',
            'Magento\Setup\Controller\License',
            'Magento\Setup\Controller\ReadinessCheckInstaller',
            'Magento\Setup\Controller\ReadinessCheckUpdater',
            'Magento\Setup\Controller\Environment',
            'Magento\Setup\Controller\DependencyCheck',
            'Magento\Setup\Controller\DatabaseCheck',
            'Magento\Setup\Controller\AddDatabase',
            'Magento\Setup\Controller\WebConfiguration',
            'Magento\Setup\Controller\CustomizeYourStore',
            'Magento\Setup\Controller\CreateAdminAccount',
            'Magento\Setup\Controller\Install',
            'Magento\Setup\Controller\Success',
            'Magento\Setup\Controller\Modules',
            'Magento\Setup\Controller\ComponentGrid',
            'Magento\Setup\Controller\StartUpdater',
            'Magento\Setup\Controller\UpdaterSuccess',
            'Magento\Setup\Controller\BackupActionItems',
            'Magento\Setup\Controller\Maintenance',
            'Magento\Setup\Controller\OtherComponentsGrid',
            'Magento\Setup\Controller\DataOption',
            'Magento\Setup\Controller\Marketplace',
            'Magento\Setup\Controller\SystemConfig',
            'Magento\Setup\Controller\InstallExtensionGrid',
            'Magento\Setup\Controller\MarketplaceCredentials',
            'Magento\Setup\Controller\Session'
        ],
        'instance' => [
            'preference' => [
                'Zend\EventManager\EventManagerInterface' => 'EventManager',
                'Zend\ServiceManager\ServiceLocatorInterface' => 'ServiceManager',
                'Magento\Framework\DB\LoggerInterface' => 'Magento\Framework\DB\Logger\Quiet',
                'Magento\Framework\Locale\ConfigInterface' => 'Magento\Framework\Locale\Config',
                'Magento\Framework\Filesystem\DriverInterface' => 'Magento\Framework\Filesystem\Driver\File',
                'Magento\Framework\Component\ComponentRegistrarInterface' =>
                    'Magento\Framework\Component\ComponentRegistrar',
            ],
        ],
    ],
];
