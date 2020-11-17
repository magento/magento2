<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console;

use Magento\Setup\Console\Command\TablesWhitelistGenerateCommand;
use Laminas\ServiceManager\ServiceManager;

/**
 * Class CommandList contains predefined list of commands for Setup.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
            \Magento\Setup\Console\Command\AdminUserCreateCommand::class,
            \Magento\Setup\Console\Command\BackupCommand::class,
            \Magento\Setup\Console\Command\ConfigSetCommand::class,
            \Magento\Setup\Console\Command\DbDataUpgradeCommand::class,
            \Magento\Setup\Console\Command\DbSchemaUpgradeCommand::class,
            \Magento\Setup\Console\Command\DbStatusCommand::class,
            \Magento\Setup\Console\Command\DependenciesShowFrameworkCommand::class,
            \Magento\Setup\Console\Command\DependenciesShowModulesCircularCommand::class,
            \Magento\Setup\Console\Command\DependenciesShowModulesCommand::class,
            \Magento\Setup\Console\Command\DiCompileCommand::class,
            \Magento\Setup\Console\Command\GenerateFixturesCommand::class,
            \Magento\Setup\Console\Command\I18nCollectPhrasesCommand::class,
            \Magento\Setup\Console\Command\I18nPackCommand::class,
            \Magento\Setup\Console\Command\InfoAdminUriCommand::class,
            \Magento\Setup\Console\Command\InfoBackupsListCommand::class,
            \Magento\Setup\Console\Command\InfoCurrencyListCommand::class,
            \Magento\Setup\Console\Command\InfoLanguageListCommand::class,
            \Magento\Setup\Console\Command\InfoTimezoneListCommand::class,
            \Magento\Setup\Console\Command\InstallCommand::class,
            \Magento\Setup\Console\Command\InstallStoreConfigurationCommand::class,
            \Magento\Setup\Console\Command\ModuleEnableCommand::class,
            \Magento\Setup\Console\Command\ModuleDisableCommand::class,
            \Magento\Setup\Console\Command\ModuleStatusCommand::class,
            \Magento\Setup\Console\Command\ModuleUninstallCommand::class,
            \Magento\Setup\Console\Command\ModuleConfigStatusCommand::class,
            \Magento\Setup\Console\Command\RollbackCommand::class,
            \Magento\Setup\Console\Command\UpgradeCommand::class,
            \Magento\Setup\Console\Command\UninstallCommand::class,
            \Magento\Setup\Console\Command\DeployStaticContentCommand::class
        ];
    }

    /**
     * Gets list of command instances.
     *
     * @return \Symfony\Component\Console\Command\Command[]
     * @throws \Exception
     */
    public function getCommands()
    {
        $commands = [];

        foreach ($this->getCommandsClasses() as $class) {
            if (class_exists($class)) {
                $commands[] = $this->serviceManager->get($class);
            } else {
                // phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception('Class ' . $class . ' does not exist');
            }
        }

        return $commands;
    }
}
