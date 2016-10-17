<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

return [
    'di' => [
        'allowed_controllers' => [
            \Magento\Setup\Controller\Index::class,
            \Magento\Setup\Controller\LandingInstaller::class,
            \Magento\Setup\Controller\LandingUpdater::class,
            \Magento\Setup\Controller\CreateBackup::class,
            \Magento\Setup\Controller\CompleteBackup::class,
            \Magento\Setup\Controller\Navigation::class,
            \Magento\Setup\Controller\Home::class,
            \Magento\Setup\Controller\SelectVersion::class,
            \Magento\Setup\Controller\License::class,
            \Magento\Setup\Controller\ReadinessCheckInstaller::class,
            \Magento\Setup\Controller\ReadinessCheckUpdater::class,
            \Magento\Setup\Controller\Environment::class,
            \Magento\Setup\Controller\DependencyCheck::class,
            \Magento\Setup\Controller\DatabaseCheck::class,
            \Magento\Setup\Controller\UrlCheck::class,
            \Magento\Setup\Controller\ValidateAdminCredentials::class,
            \Magento\Setup\Controller\AddDatabase::class,
            \Magento\Setup\Controller\WebConfiguration::class,
            \Magento\Setup\Controller\CustomizeYourStore::class,
            \Magento\Setup\Controller\CreateAdminAccount::class,
            \Magento\Setup\Controller\Install::class,
            \Magento\Setup\Controller\Success::class,
            \Magento\Setup\Controller\Modules::class,
            \Magento\Setup\Controller\ModuleGrid::class,
            \Magento\Setup\Controller\ExtensionGrid::class,
            \Magento\Setup\Controller\StartUpdater::class,
            \Magento\Setup\Controller\UpdaterSuccess::class,
            \Magento\Setup\Controller\BackupActionItems::class,
            \Magento\Setup\Controller\Maintenance::class,
            \Magento\Setup\Controller\OtherComponentsGrid::class,
            \Magento\Setup\Controller\DataOption::class,
            \Magento\Setup\Controller\Marketplace::class,
            \Magento\Setup\Controller\SystemConfig::class,
            \Magento\Setup\Controller\InstallExtensionGrid::class,
            \Magento\Setup\Controller\UpdateExtensionGrid::class,
            \Magento\Setup\Controller\MarketplaceCredentials::class,
            \Magento\Setup\Controller\Session::class,
        ],
        'instance' => [
            'preference' => [
                \Zend\EventManager\EventManagerInterface::class => 'EventManager',
                \Zend\ServiceManager\ServiceLocatorInterface::class => 'ServiceManager',
                \Magento\Framework\DB\LoggerInterface::class => \Magento\Framework\DB\Logger\Quiet::class,
                \Magento\Framework\Locale\ConfigInterface::class => \Magento\Framework\Locale\Config::class,
                \Magento\Framework\Filesystem\DriverInterface::class =>
                    \Magento\Framework\Filesystem\Driver\File::class,
                \Magento\Framework\Component\ComponentRegistrarInterface::class =>
                    \Magento\Framework\Component\ComponentRegistrar::class,
            ],
        ],
    ],
];
