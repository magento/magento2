<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Console;

use Laminas\ServiceManager\ServiceManager;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Class CommandLoader contains predefined list of commands for Setup.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CommandLoader implements CommandLoaderInterface
{
    /**
     * @var ServiceManager
     */
    private ServiceManager $serviceManager;

    /**
     * Array of command classes keyed by name
     * @var string[]
     */
    private array $commands = [
        Command\AdminUserCreateCommand::NAME => Command\AdminUserCreateCommand::class,
        Command\BackupCommand::NAME => Command\BackupCommand::class,
        Command\ConfigSetCommand::NAME => Command\ConfigSetCommand::class,
        Command\DbDataUpgradeCommand::NAME => Command\DbDataUpgradeCommand::class,
        Command\DbSchemaUpgradeCommand::NAME => Command\DbSchemaUpgradeCommand::class,
        Command\DbStatusCommand::NAME => Command\DbStatusCommand::class,
        Command\DependenciesShowFrameworkCommand::NAME => Command\DependenciesShowFrameworkCommand::class,
        Command\DependenciesShowModulesCircularCommand::NAME => Command\DependenciesShowModulesCircularCommand::class,
        Command\DependenciesShowModulesCommand::NAME => Command\DependenciesShowModulesCommand::class,
        Command\DiCompileCommand::NAME => Command\DiCompileCommand::class,
        Command\GenerateFixturesCommand::NAME => Command\GenerateFixturesCommand::class,
        Command\I18nCollectPhrasesCommand::NAME => Command\I18nCollectPhrasesCommand::class,
        Command\I18nPackCommand::NAME => Command\I18nPackCommand::class,
        Command\InfoAdminUriCommand::NAME => Command\InfoAdminUriCommand::class,
        Command\InfoBackupsListCommand::NAME => Command\InfoBackupsListCommand::class,
        Command\InfoCurrencyListCommand::NAME => Command\InfoCurrencyListCommand::class,
        Command\InfoLanguageListCommand::NAME => Command\InfoLanguageListCommand::class,
        Command\InfoTimezoneListCommand::NAME => Command\InfoTimezoneListCommand::class,
        Command\InstallCommand::NAME => Command\InstallCommand::class,
        Command\InstallStoreConfigurationCommand::NAME => Command\InstallStoreConfigurationCommand::class,
        Command\ModuleEnableCommand::NAME => Command\ModuleEnableCommand::class,
        Command\ModuleDisableCommand::NAME => Command\ModuleDisableCommand::class,
        Command\ModuleStatusCommand::NAME => Command\ModuleStatusCommand::class,
        Command\ModuleUninstallCommand::NAME => Command\ModuleUninstallCommand::class,
        Command\ModuleConfigStatusCommand::NAME => Command\ModuleConfigStatusCommand::class,
        Command\RollbackCommand::NAME => Command\RollbackCommand::class,
        Command\UpgradeCommand::NAME => Command\UpgradeCommand::class,
        Command\UninstallCommand::NAME => Command\UninstallCommand::class,
        Command\DeployStaticContentCommand::NAME => Command\DeployStaticContentCommand::class
    ];

    /**
     *
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Generate a symfony console command with the laminas service manager
     *
     * @param string $name
     * @return SymfonyCommand
     * @throws CommandNotFoundException
     */
    public function get(string $name): SymfonyCommand
    {
        if ($this->has($name)) {
            /** @var SymfonyCommand $command */
            $command = $this->serviceManager->get($this->commands[$name]);
            return $command;
        }

        throw new CommandNotFoundException(sprintf('Command "%s" does not exist.', $name));
    }

    /**
     * Return whether the command loader has a command configured for given $name
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * Return the list of configured commands for the command loader
     *
     * @return string[]
     */
    public function getNames(): array
    {
        return array_keys($this->commands);
    }
}
