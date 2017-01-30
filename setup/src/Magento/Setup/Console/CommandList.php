<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;

use Zend\ServiceManager\ServiceManager;

/**
 * Class CommandList contains predefined list of commands for Setup
 */
class CommandList
{
    /**
     * Service Manager
     *
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * Constructor
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Gets list of setup command classes
     *
     * @return string[]
     */
    protected function getCommandsClasses()
    {
        return [
            'Magento\Setup\Console\Command\AdminUserCreateCommand',
            'Magento\Setup\Console\Command\BackupCommand',
            'Magento\Setup\Console\Command\ConfigSetCommand',
            'Magento\Setup\Console\Command\CronRunCommand',
            'Magento\Setup\Console\Command\DbDataUpgradeCommand',
            'Magento\Setup\Console\Command\DbSchemaUpgradeCommand',
            'Magento\Setup\Console\Command\DbStatusCommand',
            'Magento\Setup\Console\Command\DependenciesShowFrameworkCommand',
            'Magento\Setup\Console\Command\DependenciesShowModulesCircularCommand',
            'Magento\Setup\Console\Command\DependenciesShowModulesCommand',
            'Magento\Setup\Console\Command\DiCompileCommand',
            'Magento\Setup\Console\Command\DiCompileMultiTenantCommand',
            'Magento\Setup\Console\Command\GenerateFixturesCommand',
            'Magento\Setup\Console\Command\I18nCollectPhrasesCommand',
            'Magento\Setup\Console\Command\I18nPackCommand',
            'Magento\Setup\Console\Command\InfoAdminUriCommand',
            'Magento\Setup\Console\Command\InfoBackupsListCommand',
            'Magento\Setup\Console\Command\InfoCurrencyListCommand',
            'Magento\Setup\Console\Command\InfoLanguageListCommand',
            'Magento\Setup\Console\Command\InfoTimezoneListCommand',
            'Magento\Setup\Console\Command\InstallCommand',
            'Magento\Setup\Console\Command\InstallStoreConfigurationCommand',
            'Magento\Setup\Console\Command\ModuleEnableCommand',
            'Magento\Setup\Console\Command\ModuleDisableCommand',
            'Magento\Setup\Console\Command\ModuleStatusCommand',
            'Magento\Setup\Console\Command\ModuleUninstallCommand',
            'Magento\Setup\Console\Command\MaintenanceAllowIpsCommand',
            'Magento\Setup\Console\Command\MaintenanceDisableCommand',
            'Magento\Setup\Console\Command\MaintenanceEnableCommand',
            'Magento\Setup\Console\Command\MaintenanceStatusCommand',
            'Magento\Setup\Console\Command\RollbackCommand',
            'Magento\Setup\Console\Command\UpgradeCommand',
            'Magento\Setup\Console\Command\UninstallCommand',
        ];
    }

    /**
     * Gets list of command instances
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Exception
     */
    public function getCommands()
    {
        $commands = [];

        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->serviceManager->create($class);
            } else {
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
